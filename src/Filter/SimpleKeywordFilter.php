<?php

namespace DARCNews\Filter;
use DARCNews\Core\ErrorCodes;

/**
 * Einfacher Keyword filter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class SimpleKeywordFilter extends FilterBase
{
    /** @var string PARAM_TITELOPERATOR Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TITELOPERATOR = 'TitelOperator';

    /** @var string PARAM_TITELKEYWORDS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TITELKEYWORDS = 'TitelKeywords';

    /** @var string PARAM_TEASEROPERATOR Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TEASEROPERATOR = 'TeaserOperator';

    /** @var string PARAM_TEASERKEYWORDS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TEASERKEYWORDS = 'TeaserKeywords';

    /** @var string PARAM_TEXTOPERATOR Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TEXTOPERATOR  = 'TextOperator';

    /** @var string PARAM_TEXTKEYWORDS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TEXTKEYWORDS  = 'TextKeywords';

    /** @var string PARAM_MATCHOPERATOR Konstante für Zugriff auf Parameters-Array */
    private const PARAM_MATCHOPERATOR = 'MatchOperator';

    /** @var string PARAM_ERGEBNIS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_ERGEBNIS      = 'Ergebnis';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected /*override*/array $parameters = [
        self::PARAM_TITELOPERATOR => [
            'Value' => 'and',
            'Description' => "Verknüpfung der Keywords im Titel (and, or)",
            'MultiValue' => false
        ],
        self::PARAM_TITELKEYWORDS => [
            'Value' => [],
            'Description' => "Keywords, die im Titel enthalten sein müssen",
            'MultiValue' => true
        ],
        self::PARAM_TEASEROPERATOR => [
            'Value' => 'and',
            'Description' => "Verknüpfung der Keywords im Teaser (and, or)",
            'MultiValue' => false
        ],
        self::PARAM_TEASERKEYWORDS => [
            'Value' => [],
            'Description' => "Keywords, die im Teaser enthalten sein müssen",
            'MultiValue' => true
        ],
        self::PARAM_TEXTOPERATOR => [
            'Value' => 'and',
            'Description' => "Verknüpfung der Keywords im Nachrichtentext (and, or)",
            'MultiValue' => false
        ],
        self::PARAM_TEXTKEYWORDS => [
            'Value' => [],
            'Description' => "Keywords, die im Nachrichtentext enthalten sein müssen",
            'MultiValue' => true
        ],
        self::PARAM_MATCHOPERATOR => [
            'Value' => 'and',
            'Description' => "Verknüpfung der Keyword-Prüfung von Titel, Teaser, Text (and, or)",
            'MultiValue' => false
        ],
        self::PARAM_ERGEBNIS => [
            'Value' => 'Match',
            'Description' => "Ergebnis des Filters, wenn die Keyword-Prüfung erfolg hatte (Match, NoMatch)",
            'MultiValue' => false
        ],
    ];

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return <<<END
        Ein einfacher Keyword-Filter, der im Titel, Teaser und Nachrichtentext nach Suchbegriffen suchen kann.
        Die Suchbegriffe in den 3 Texten können dabei entweder mit AND oder OR verknüpft werden.
        Weiterhin können die 3 Teilergebnisse mit AND oder OR zu einem Gesamtergebnis verknüpft werden.
        Über den Parameter "Ergebnis" kann entschieden werden, ob ein Treffer den Filter Matchen oder nicht-matchen lässt.
        END;
    }


    /**
     * Überladene Version von setParameter, da die Parameter ...operator und Ergebnis geprüft werden
     * @param string $Parameter Name des Parameters
     * @param mixed $Value      Wert des Parameters
     * @return int              Returncode ob die Funktion erfolgreich war
     */
    public /*override*/function setParameter(string $Parameter, mixed $Value): int
    {
        // Wenn der parameter auf "Operator" endet, dann prüfen wir erlaubte Werte
        if (strpos(strtolower($Parameter), 'operator') !== false)
        {
            if (!in_array(strtolower($Value), ['and', 'or']))
                return ErrorCodes::InvalidValue;
        }

        // Erlaubte Werte für Ergebnis prüfen
        if (strtolower($Parameter) == 'ergebnis')
        {
            if (!in_array(strtolower($Value), ['match', 'nomatch']))
                return ErrorCodes::InvalidValue;
        }

        // Wenns die Prüfungen erfolgreich waren, normale Parameter-Verarbeitung durch Basisklasse
        return parent::setParameter($Parameter, $Value);
    }

    /**
     * Prüft, ob in den Arrays irgendwas enthalten ist, so dass der Filter sinnvoll laufen kann
     * @return bool TRUE, wenn der Filter arbeiten kann, ansonsten FALSE
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return (count(array_merge(
            $this->getParameter(self::PARAM_TITELKEYWORDS),
            $this->getParameter(self::PARAM_TEXTKEYWORDS),
            $this->getParameter(self::PARAM_TEXTKEYWORDS)
        )) > 0);
    }

    /**
     * Führt die Keyword-Überprüfung aus
     * @return int 1 wenn die Nachricht erfolgreich verarbeitet wurde
     */
    protected /*abstractImpl*/ function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message === false)
            return 0;

        $titelKW = $this->getParameter(self::PARAM_TITELKEYWORDS);
        $titelOP = $this->getParameter(self::PARAM_TITELOPERATOR);
        $titelMatch = $this->checkForKeywords($message->getTitel(), $titelKW, $titelOP); // true/false/null

        $teaserKW = $this->getParameter(self::PARAM_TEASERKEYWORDS);
        $teaserOP = $this->getParameter(self::PARAM_TEASEROPERATOR);
        $teaserMatch = $this->checkForKeywords($message->getTeaser(), $teaserKW, $teaserOP); // true/false/null

        $textKW = $this->getParameter(self::PARAM_TEXTKEYWORDS);
        $textOP = $this->getParameter(self::PARAM_TEXTOPERATOR);
        $textMatch = $this->checkForKeywords($message->getText(), $textKW, $textOP); // true/false/null

        $matchOp = $this->getParameter(self::PARAM_MATCHOPERATOR);

        switch (strtolower($matchOp))
        {
            case 'and':
                $isMatch = ($titelMatch ?? true) && ($teaserMatch ?? true) && ($textMatch ?? true);
                break;
            case 'or':
                $isMatch = ($titelMatch ?? false) || ($teaserMatch ?? false) || ($textMatch ?? false);
                break;
        }

        if (strtolower($this->getParameter(self::PARAM_ERGEBNIS) ?? "") == 'nomatch')
            $isMatch = !(bool) $isMatch;

        $this->setMessageProcessed($message, $isMatch);

        return 1;
    }

    /**
     * Prüft den Text auf Keywords
     * @param string $Text          Der Text, der durchsucht werden soll
     * @param array $KeywordArr     Die Keywords
     * @param string $Operator      der Verknüpfungsoperator
     * @return bool|null            Logisches ergebnis oder NULL, wenn keine Keywords
     */
    private function checkForKeywords(string $Text, array $KeywordArr, string $Operator): ?bool
    {
        $match = null;

        foreach ($KeywordArr as $kw)
        {
            $check = (strpos($Text, $kw) !== false);
            switch (strtolower($Operator))
            {
                case 'and':
                    $match = ($match ?? true) && $check;
                    break;
                case 'or':
                    $titelMatch = ($match ?? false) || $check;
                    break;
            }
        }
        return $match;
    }
}