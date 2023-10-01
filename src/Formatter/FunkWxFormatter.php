<?php

namespace DARCNews\Formatter;

use DARCNews\Core\Logger;
use DARCNews\Core\ErrorCodes;

/**
 * FunkWX Formatter formatiert FunkWX Nachrichten
 * Es können der Fließtext und/oder die QAM behalten werden.
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class FunkWxFormatter extends FormatterBase
{
    /** @var string PARAM_MAXCHARACTERS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_KEEPTEXT = 'KeepText';

    /** @var string PARAM_ERSETZEN Konstante für Zugriff auf Parameters-Array */
    private const PARAM_KEEPQAM = 'KeepQAM';

     /** @var string PARAM_ERSETZEN Konstante für Zugriff auf Parameters-Array */
     private const PARAM_HASHTAGS = 'Hashtags';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [

        self::PARAM_KEEPTEXT => [
            'Description' => "Ausführlichen Text behalten? 1 = Ja, 0 = Nein",
            'MultiValue' => false,
            'Value' => "1",
        ],

        self::PARAM_KEEPQAM => [
            'Description' => "Den Wetterbericht im Telegramm-Format, von ZCZC bis NNNN behalten? 1 = Ja, 0 = Nein",
            'MultiValue' => false,
            'Value' => "1",
        ],

        self::PARAM_HASHTAGS => [
            'Description' => "Hashtags, die an die Meldung angehängt werden sollen",
            'MultiValue' => true,
            'Value' => ["#hamradio", "#hamr", "#swl", "#spaceweather", "#spacewx", "#solarwx", "#hfprop"],
        ],

    ];
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Dieser Formatter extrahiert entweder den Fließtext oder die QAM-Nachricht oder beides.";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        // einer von beiden sollte auf 1 gesetzt werden, sonst ist der Formatter sinnlos
        return (intval($this->getParameter(self::PARAM_KEEPTEXT))
            + intval($this->getParameter(self::PARAM_KEEPQAM))) > 0;
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
        if( (substr(strtolower($Parameter), 0,2) == 'ke') && (!in_array($Value, ['0', '1'])))
            return ErrorCodes::InvalidValue;

        return parent::setParameter($Parameter, $Value);
    }

    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        $text = $message->getTextAsText();

        if (true)
        {
            // Alles nach NNNN erst einmal wegschneiden
            $nnnn = strpos($text, 'NNNN');

            if ($nnnn > 0)
                $text = substr($text, 0, $nnnn + 4);
        }

        // Wenn Text nicht behalten werden soll, dann alles vor ZCZC wegschneiden
        if ($this->getParameter(self::PARAM_KEEPTEXT) == "0")
        {
            $zczc = strpos($text, 'ZCZC');

            if ($zczc > 0)
                $text = substr($text, $zczc);
        }

        // Wenn QAM nicht behalten werden soll, dann alles von ZCZCZ bis NNNN wegschneiden
        if ($this->getParameter(self::PARAM_KEEPQAM) == "0")
        {
            $zczc = strpos($text, 'ZCZC');
            $nnnn = strpos($text, 'NNNN');

            if (($zczc > 0) && ($nnnn > 0))
            {
                $text = substr($text, 0, $zczc) . substr($text, $nnnn + 4);
            }
        }
       
        // Wenn beide Texte behalten werden, Zeilenumbruch einfügen
        if (($this->getParameter(self::PARAM_KEEPTEXT) == "1") && ($this->getParameter(self::PARAM_KEEPQAM) == "1"))
        {
            $text = str_replace("ZCZC", "\n\nZCZC", $text);
        }

        $text = trim($text);

        // jetzt noch Hashtags hinzufügen, wenn vorhanden
        $hashtagArr = $this->getParameter(self::PARAM_HASHTAGS);
        if (count($hashtagArr) > 0)
        {
            $text .= "\n" . implode(" ", $hashtagArr);
        }

        $message->setText($text);
        $result = $this->saveMessage($message);

        if ($result == ErrorCodes::AlreadyExists)
        {
            Logger::Error("Die Meldung mit der ID {$message->getId()} ist bereits vorhanden\n");
            $this->setMessageProcessed($message, false);
            return 1; // wir haben eine Nachricht verarbeitet
        }

        $this->setMessageProcessed($message, true);
        return 1;
    }
}