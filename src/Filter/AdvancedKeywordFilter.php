<?php

namespace DARCNews\Filter;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;
use DARCNews\Core\News;

/**
 * Der AdvancedKeywordFilter kann eine Nachricht mit einer komplexen Keyword-Logik filtern
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class AdvancedKeywordFilter extends FilterBase
{
    /** @var string P_KEYWORDS   Konstante für den Keywords-parameter, da der String doch öfter vorkommt */
    private const P_KEYWORDS = 'Keywords';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected /*override*/array $parameters = [
       self::P_KEYWORDS => [
            'Value' => [],
            'Description' => <<<END
            Das Format für diesen Parameter ist:
            <+-> <Operator> <Nachrichtenteil> <Position> [Nicht] : <Suchbegriff>
            \n
            + oder -:
              Hinzufügen oder entfernen des Suchbegriffs beim Set-Befehl
            \n
            Operatoren:
              & - logisches UND mit dem vorherigen Ergebnis
              | - logisches OR  mit dem vorherigen Ergebnis
              # - wenn Suchbegriff gefunden, sofort Ende mit "Filter Erfolgreich"
              ^ - wenn Suchbegriff gefunden, sofort Ende mit "Filter nicht erfolgreich"
            \n
            Wichtig: Keywords mit den Operatoren & oder | werden vollständig, der Reihe nach ausgewertet.
                     Klammern sind nicht möglich, also aufpassen, denn ein OR nach einem AND kann wieder zu einem Treffer führen.
            \n
            Nachrichtenteil:
              T - Titel der Nachricht
              U - Untertitel/Teaser der Nachricht
              N - Nachrichtentext
            \n
            Position:
              > - beginnt am Anfang des gewählten Nachrichtenteils
              * - enthält an beliebiger Stelle, auch als Wortbestandteil
              _ - enthält als einzelnes Wort
              $ - Expertenmodus: Der Suchbegriff ist ein PCRE2-kompatibler, regulärer Suchausdruck
            \n
            Nicht:
              ~ - invertiert die Logik
            \n
             Suchbegriff:
                  Ein oder mehrere Worte. Leerzeichen am Anfang und Ende werden entfernt (TRIM)
                  Wichtig: Der Doppelpunkt trennt den Operator vom Suchwort und muss angegeben werden!
            \n
            END,
            'MultiValue' => true
        ],
       

    ];
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return <<<END
        Der AdvancedKeywordFilter kann den Inhalt der Nachricht nach Suchwörtern durchsuchen und diese logisch verknüpfen.
        Zwar lassen sich Suchausdrücke nicht mit "(" und ")" schachteln, dennoch können rechtkomplexe Ausdrücke gebildet werden.
        Alle vorhandenen Suchausdrücke werden bis zum Ende abgearbeitet. Da ein "OR 1" ein vorangegangenes "AND 0" wieder
        aufhebt, sollten zuerst alle OR-Ausdrücke und anschließend die AND Ausdrücke eingefügt werden.
        Zusätzlich gibt es die Joker # = sofortiger Treffer und ^ = sofortiger Nicht-Treffer, mit denen sich 
        Ausschlusskriterien setzen lassen.
        \n
        END;
    }


    /**
     * Überschriebene Version von setParameter, da für die Keywords eine besondere Syntax notwendig ist.
     * Diese Syntax wird geprüft
     * @param string $Parameter Name des Parametes
     * @param mixed $Value      Wert des Parameters
     * @return int              ErrorCode, obs erfolgreich war
     */
    public /*override*/function setParameter(string $Parameter, mixed $Value): int
    {
        // Wenn der parameter auf "Operator" endet, dann prüfen wir erlaubte Werte
        if (strncasecmp(self::P_KEYWORDS, $Parameter, strlen($Parameter)) == 0)
        {
            $parts = explode(':', $Value);
            if (count($parts) != 2)
                return ErrorCodes::NotEnoughArguments;

            $operators = [];
            // Formatprüfung auf: [&|#^] [TUN] [>*_$] [~ ] jeweils mit leerzeichen egal
            if (!preg_match('/([&|#^]{1})\\s*([TUN]{1})\\s*([>*_$]{1})\\s*(~?)/siu', $parts[0], $operators))
                return ErrorCodes::InvalidValue;

            if ($operators[3] == '$')   // expertenmodus gewünscht, dann Regex prüfen
            {
                if (@preg_match($parts[1], null) === false)
                    return ErrorCodes::InvalidValue;
            }
        }

        // Wenns die Prüfung erfolgreich war, normale Parameter-Verarbeitung durch Basisklasse
        return parent::setParameter($Parameter, $Value);
    }

    /**
     * Prüft, ob Keywords vorhanden sind. Ohne ist der Filter nämlich sinnlos
     * @return bool TRUE, wenn gültige Keywords vorhanden sind. Andernfalls FALSE
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return count($this->getParameter(self::P_KEYWORDS)) > 0;
    }

    /**
     * Führt die Keyword-Überprüfung aus
     * @return int 1 wenn die Nachricht erfolgreich verarbeitet wurde
     */
    protected /*abstractImpl*/ function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        // na dann los
        $result = $this->processKeywords($message);
    
        $this->setMessageProcessed($message, $result);

        return 1;
    }

    /**
     * Führt die Keyword-Überprüfung durch.
     * Implementiert als eigene Funktion, damit man die Klasse ableiten und erweitern kann
     * @param News $message     Message-Struktur (weil man in titel, teaser und text suchen kann)
     * @return bool             Ergebnis der logischen Prüfung
     */
    protected function processKeywords(News $message) : bool
    {
        $logicalResult = null;

       
        foreach ( $this->getParameter(self::P_KEYWORDS) as $operator_keyword)
        {
            list($operator, $keyword) = explode(':', $operator_keyword);
            $keyword = trim($keyword);  // aufräumen

            Logger::Info(static::class . " ({$this->getName()}): Verarbeite Operator {$operator} für Keyword '{$keyword}'");

            $operators = [];
            //            Gruppe 1       Gruppe 2      Gruppe 3      Gruppe 4
            preg_match('/([&|#^]{1})\\s*([TUN]{1})\\s*([>*_]{1})\\s*(~?)/siu', $operator, $operators);

            $text = match ($operators[2])
            {
                'T' => $message->getTitel(),
                'U' => $message->getTeaser(),
                'N' => $message->getText(),
            };

            Logger::Debug(preg_quote($text));
            $boolean = match ($operators[3])
            {
                '>' => preg_match('/^' . preg_quote($keyword, '/') . '/u', $text),
                '*' => preg_match('/' . preg_quote($keyword, '/') . '/u', $text),
                '_' => preg_match('/\b' . preg_quote($keyword, '/') . '/u', $text),
                '$' => preg_match($keyword, $text),
            };

            if ($operators[4] == '~')
                $boolean = !(bool)$boolean;

            Logger::Debug("Ergebnis der Prüfung (und ggf. ~) ist: ". ($boolean ? "TRUE":"FALSE"));

            switch ($operators[1])
            {
                case '&':
                    Logger::Debug("Verknüpfe mit UND");
                    $logicalResult = ($logicalResult ?? true) && $boolean;
                    break;

                case '|':
                    Logger::Debug("Verknüpfe mit OR");
                    $logicalResult = ($logicalResult ?? false) || $boolean;
                    break;

                case '#':
                    Logger::Debug("True-Joker ist: ". ($boolean ? "TRUE":"FALSE"));
                    if ($boolean)
                    {
                        $logicalResult = true;
                        break 2;    // ganz raus, auch aus der foreach
                    }
                    break;

                case '^':
                    Logger::Debug("False-Joker ist ". ($boolean ? "TRUE":"FALSE"));
                    if ($boolean)
                    {
                        $logicalResult = false;
                        break 2;    // ganz raus, auch aus der foreach
                    }
                    break;
            }            
        }    

        Logger::Debug("Gesamtergebnis ist ". ($logicalResult ? "TRUE":"FALSE"));

        if (is_null($logicalResult))
        {
            Logger::Warn(static::class . " ({$this->getName()}): Gesamtergebnis ist NULL, bitte Log auf Fehler überprüfen!");
            $logicalResult = false;
        }

        return $logicalResult; // rückgabe des Gesamtergebnis
    }
}
