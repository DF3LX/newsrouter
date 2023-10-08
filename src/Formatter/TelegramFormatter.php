<?php

namespace DARCNews\Formatter;

/**
 * Summary of TelegramFormatter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class TelegramFormatter extends FormatterBase
{
    /**
      * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
    */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return "Ich Formatiere nachrichten für Telegram";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return true;
    }
    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/ function doStuff():int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        $text = $message->getTextAsText();

        $text = trim($text);

        $message->setText($text);

        $this->setMessageProcessed($message, true);
        return 1;
    }
}
