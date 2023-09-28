<?php

namespace DARCNews\Filter;
use DARCNews\Core\ErrorCodes;

/**
 * Summary of SourceFilter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class SourceFilter extends FilterBase
{
    /** @var string PARAM_SOURCE Konstante für Zugriff auf Parameters-Array */
    private const PARAM_SOURCE = 'Source';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_SOURCE => [
            'Value' => 0,
            'Description' => "Name der Quelle, auf die gefiltert werden soll",
            'MultiValue' => false
        ],
    ];

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return "ich bin der Filter der auf die Quelle prüft";
    }

    /**
     * Summary of getParameter
     * @param string $Parameter
     * @return mixed
     */
    public /*override*/ function getParameter(string $Parameter): mixed
    {
        // Wenn er Parameter "source" ist, dann ermitteln wir selbst den Namen für die gespeichrete SourceId
        if (strtolower($Parameter ?? "") == strtolower(self::PARAM_SOURCE))
        {
            global $pdo;
            $statement = $pdo->prepare("SELECT Name from Source where Id = :id");
            $statement->bindValue(":id", parent::getParameter(self::PARAM_SOURCE));
            $statement->execute();
            $name = $statement->fetchColumn();
            $statement->closeCursor();
            $statement = null;

            return $name;
        }
 
        // Wenns nicht der Source-Parameter ist, normale Parameter-Verarbeitung durch Basisklasse
        return parent::getParameter($Parameter);
    }

     /**
     * Überschriebene Version von setParameter, da der Parameter "source" über die datenbank aufgelöst werden muss.
     * @param string $Parameter Name des Parametes
     * @param mixed $Value      Wert des Parameters
     * @return int              ErrorCode, obs erfolgreich war
     */
    public /*override*/ function setParameter(string $Parameter, mixed $Value): int
    {
        // Wenn der parameter "source" ist, müssen wir aus dem Namen erst die SourceId basteln
        if (strtolower($Parameter ?? "") == strtolower(self::PARAM_SOURCE))
        {
            global $pdo;
            $statement = $pdo->prepare("SELECT Id from Source where Name = :Name");
            $statement->bindValue(":Name", $Value);
            $statement->execute();
            $Value = $statement->fetchColumn();
            $statement->closeCursor();
            $statement = null;

            if ($Value === false)
                return ErrorCodes::ValueNotFound;
        }

        // Wenns nicht der Source-Parameter ist, normale Parameter-Verarbeitung durch Basisklasse
        return parent::setParameter($Parameter, $Value);
    }

    
    /**
     * Prüft, ob eine gültige Source gesetzt wurde.
     * @return bool TRUE wenn der Filter aktiviert werden kann
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        // Enable ist möglich, sobald der Filter konfiguriert ist.
        // Abfrage über Parent, da wir die ID und nicht den Namen wollen
        return (parent::getParameter(self::PARAM_SOURCE) > 0);
    }

    /**
     * Führt die Source-Überprüfung aus
     * @return int 1 wenn die Nachricht erfolgreich verarbeitet wurde
     */
    protected /*abstractImpl*/ function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        $isMatch = (parent::getParameter(self::PARAM_SOURCE) == $message->getSourceId());
    
        $this->setMessageProcessed($message, $isMatch);

        return 1;
    }
}
