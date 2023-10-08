<?php

namespace DARCNews\Formatter;
use DARCNews\Core\ByState;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;
use DARCNews\Core\ModuleBase;
use DARCNews\Core\News;

/**
 * Summary of FormatterBase
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class FormatterBase extends ModuleBase
{
     /** @var string PARAM_CHANNELID Konstante für Zugriff auf Parameters-Array */
     private const PARAM_CHANNELID = 'channelid';
     
     /** @var string PARAM_FILTERID Konstante für Zugriff auf Parameters-Array */
     private const PARAM_FILTERID = 'filterid';

    /** @var ?int $_channelId ID des verlinkten Ausgabekanals  */
    private ?int $_channelId;

    /** @var ?int $_filterId * ID des verlinkten Filters  */
    private ?int $_filterId;

    /**
     * Überschriebene Funktion Load, die zusätzlich noch die ChannelId und FilterId setzt
     * @return array|null   Settings zur weiteren Verarbeitung
     */
    protected /*override*/ function load(): ?array
    {
        $settings = parent::load();

        if ($settings != null)
        {
            $this->_channelId = $settings[self::PARAM_CHANNELID];
            $this->_filterId = $settings[self::PARAM_FILTERID];
        }

        return $settings;
    }

    /**
     * Prüft, ob die zusätzlichen Parameter gesetzt sind
     * @return bool TRUE wenn alles richtig gesetzt
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return ($this->_channelId ?? 0 > 0) && ($this->_filterId ?? 0 > 0);
    }

    // /**
    //  * Property Getter für ChannelId
    //  * @return int|null der gesetzte Channel
    //  */
    // protected final function getChannelId(): ?int
    // {
    //     return $this->_channelId;
    // }

    //  /**
    //  * Property Getter für FilterId
    //  * @return int|null der gesetzte Filter
    //  */
    // protected final function getFilterId(): ?int
    // {
    //     return $this->_filterId;
    // }

    /**
     * Überschriebene Version von getParameter um für die beiden Parameter Channel und Filter die Namen zurück zu geben
     * @param string $Parameter Name des Parameters
     * @return mixed            Wert des Parameters
     */
    public /*override*/function getParameter(string $Parameter): mixed
    {
        global $pdo;

        if (substr(strtolower($Parameter), 0, 2) == "ch") // Channel
        {
            // Lookup des Channel-Namens. 
            // TODO warum joine ich mir da eigentlich den konfigurierten Wert zusammen, ansttt einfach $this->_channelId abzufragen? 
            $statement = $pdo->prepare("SELECT channel.name from channel inner join formatter on formatter.channelid = channel.id where formatter.id = :id");
            $statement->bindValue(":id", $this->getId());
            $statement->execute();
            $name = $statement->fetchColumn();
            $statement->closeCursor();
            $statement = null;

            return $name;
        }

        if (substr(strtolower($Parameter), 0, 2) == "fi") // Filter
        {
            // Lookup des Filter-Namens. 
            // TODO warum joine ich mir da eigentlich den konfigurierten Wert zusammen, ansttt einfach $this->_filterId abzufragen? 
            $statement = $pdo->prepare("SELECT filter.name from filter inner join formatter on formatter.filterid = filter.id where formatter.id = :id");
            $statement->bindValue(":id", $this->getId());
            $statement->execute();
            $name = $statement->fetchColumn();
            $statement->closeCursor();
            $statement = null;

            return $name;
        }

        // Wenns weder channel noch Filter ist, normale Parameter-Verarbeitung durch Basisklasse
        return parent::getParameter($Parameter);
    }


    /**
     * Überschriebene Version von setParameter um die beiden Parameter Channel  und Filter setzen zu können
     * @param string $Parameter Name des Parameters
     * @param mixed  $Value     Wert des Parameters
     * @return int              ErrorCode obs geklappt hat
     */
    public function setParameter(string $Parameter, mixed $Value): int
    {
        global $pdo;
        
        if (substr(strtolower($Parameter), 0, 2) == "ch") // Channel
        {
            // Lookup der ID anhand des Namens
            $statement = $pdo->prepare("SELECT id from channel where name = :name");
            $statement->bindValue(":name", $Value);
            $statement->execute();
            $channelId = $statement->fetchColumn();
            $statement->closeCursor();

            if ($channelId === false)
                return ErrorCodes::ValueNotFound;

            // Speichern der Einstellungen
            $statement = $pdo->prepare("UPDATE formatter set channelid = :channelid where id = :id");
            $statement->bindValue(":channelid", $channelId);
            $statement->bindValue(":id", $this->getId());
            $statement->execute();
            $statement->closeCursor();
            $statement = null;

            $this->_channelId = $channelId; // internen Wert setzen nicht vergessen ;)
            return ErrorCodes::OK;
        }

        if (substr(strtolower($Parameter), 0, 2) == "fi") // Filter
        {
            // Lookup der ID anhand des Namens
            $statement = $pdo->prepare("SELECT id from filter where name = :name");
            $statement->bindValue(":name", $Value);
            $statement->execute();
            $filterId = $statement->fetchColumn();
            $statement->closeCursor();

            if ($filterId === false)
                return ErrorCodes::ValueNotFound;

            // Speichern der Einstellungen
            $statement = $pdo->prepare("UPDATE formatter set filterid = :filterid where id = :id");
            $statement->bindValue(":filterid", $filterId);
            $statement->bindValue(":id", $this->getId());
            $statement->execute();
            $statement->closeCursor();
            $statement = null;

            $this->_filterId = $filterId; // internen Wert setzen nicht vergessen ;)
            return ErrorCodes::OK;
        }

        // Wenns weder channel noch Filter ist, normale Parameter-Verarbeitung durch Basisklasse
        return parent::setParameter($Parameter, $Value);
    }

    /**
     * Überschriebene version von getParameterInfo() um die beiden Sonderparameter Filter und Channel mit rein zu mergen
     * @return array    Parameter-Info-Struktur
     */
    public /*override*/ function getParameterInfo(): array
    {
        $arr = [
           self::PARAM_CHANNELID => [
                'Value' => $this->_channelId,
                'Description' => "Der mit diesem Formatter verlinkte Ausgabekanal",
                'MultiValue' => false
            ],
            self::PARAM_FILTERID => [
                'Value' => $this->_filterId,
                'Description' => "Der Filter, der diesen Formatter aktiviert",
                'MultiValue' => false
            ],
        ];

        return array_merge($this->parameters, $arr);
    }


    /**
     * Eine unbearbeitete Nachricht zurückgeben
     * @return array|bool   Nachricht oder bool false, wenn nix da
     */
    protected function getUnprocessedMessage(): ?News
    {
        global $pdo;
        $statement = $pdo->prepare("SELECT IM.*, IMD.* FROM InputMessage IM INNER JOIN InputMessageData IMD on IM.ID = IMD.ID" // InpuitMessage und Daten
            . " INNER JOIN FilteredBy  Fil on IM.ID = Fil.InputMessageId and Fil.FilterID = :filterid and Fil.state = " . ByState::Successful // wo der Filter (ohne Formatter) Match sagt
            . "  LEFT JOIN FormattedBy Fmt on IM.ID = Fmt.InputMessageId and Fmt.FilterID = :filterid and Fmt.FormatterId = :formatterid WHERE Fmt.InputMessageId IS NULL"); // und dieser Formatter noch nicht dran war
        $statement->bindValue(":filterid", $this->_filterId);
        $statement->bindValue(":formatterid", $this->getId());
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $statement = null;

        if ($result != null)
            return News::CreateFromDB($result);
        else
            return null;
    }

    /**
     * Nachricht auf verarbeitet setzen
     * @param int $MessageId    ID der Nachricht
     * @param bool $Erfolg      Flag, ob erfolgreich verarbeitet oder nicht
     * @return void             nix, gar nix
     */
    protected function setMessageProcessed(News $Message, bool $Erfolg): void
    {
        global $pdo;
        $statement = $pdo->prepare("INSERT INTO FormattedBy(InputMessageId, FilterId, FormatterID, State) VALUES(:inputmessageid, :filterid, :formatterid, :state)");
        $statement->bindValue(":inputmessageid", $Message->getId());
        $statement->bindValue(":filterid", $this->_filterId);
        $statement->bindValue(":formatterid", $this->getId());
        $statement->bindValue(":state", (
            $this->isCatchUp() // Wenn CatchUp, dann werden die Nachrichten als CatchUp markiert
            ? ByState::CatchUp
            : ($Erfolg
                ? ByState::Successful
                : ByState::Error)
        ));
        $statement->execute();
        $statement->closeCursor();
        $statement = null;
    }

    /**
     * Prüft, ob die Nachricht schon vorhanden ist
     * @param \DARCNews\Core\News $Message  Nachrichtenstruktur
     * @return bool             TRUE wenn Nachricht bereits gespeichert
     */
    protected function isMessageKnown(News $Message ): bool
    {
        global $pdo;
        $statement = $pdo->prepare("SELECT count(*) from OutputMessage where FormatterID = :formatterid and ChannelId = :channelid and InputMessageId = :inputmessageid and Sequence = :sequence");
        $statement->bindValue(":formatterid", $this->getId());
        $statement->bindValue(":channelid", $this->_channelId);
        $statement->bindValue(":inputmessageid", $Message->getId());
        $statement->bindValue(":sequence", $Message->getSequence());
        $statement->execute();
        $foundRows = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;

        return ($foundRows > 0);
    }
    
    /**
     * Speichert eine ausehende Nachricht ab
     * @param \DARCNews\Core\News $Message  Nachrichtenstruktur
     * @return int                          ErrorCode obs geklappt hat
     */
    protected function saveMessage(News $Message):int
    {
        // Keine Duplikate bitte
        if ($this->isMessageKnown($Message))
            return ErrorCodes::AlreadyExists;

        // im Modus CatchUp werden keine ausgehenden Nachrichten gespeichert
        if ($this->isCatchUp())
            return ErrorCodes::OK;

        global $pdo;
        $pdo->beginTransaction();
        $statement = $pdo->prepare("INSERT INTO OutputMessage(FormatterId, ChannelId, InputMessageId, Sequence) VALUES(:formatterid, :channelid, :inputmessageid, :sequence) RETURNING Id");
        $statement->bindValue(":formatterid", $this->getId());
        $statement->bindValue(":channelid", $this->_channelId);
        $statement->bindValue(":inputmessageid", $Message->getId());
        $statement->bindValue(":sequence", $Message->getSequence());
        $statement->execute();
        $dbId = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;

        $statement = $pdo->prepare("INSERT INTO OutputMessageData(Id, Titel, Teaser, Text, Permalink, Image, Metadata) VALUES(:id, :titel, :teaser, :text, :permalink, :image, :metadata)");
        $statement->bindValue(":id", $dbId);
        $statement->bindValue(":titel", $Message->getTitel());
        $statement->bindValue(":teaser", $Message->getTeaser());
        $statement->bindValue(":text", $Message->getText());
        $statement->bindValue(":permalink", $Message->getPermalink());
        $statement->bindValue(":image", $Message->getImage(), \PDO::PARAM_LOB);
        $statement->bindValue(":metadata", $Message->getMetadataString());
        $statement->execute();
        $statement->closeCursor();
        $statement = null;
        $pdo->commit();

        return ErrorCodes::OK;
    }
}