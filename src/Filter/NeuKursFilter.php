<?php

namespace DARCNews\Filter;

/**
 * Summary of NeuKursFilter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
*/
class NeuKursFilter extends FilterBase
{
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */

    public static /*abstactImpl*/ function getDescription(): string
    {
        return "ich bin der Filter für neue AFU-Kurse";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * Da der Filter ein spezifischer Filtert ist, sind keine Vorbedingungen notwendig
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return true;
    }


    /**
     * Prüft, ob die Metadaten die Daten zu einem Kurs enthalten
     * @return int 1 wenn die Nachricht erfolgreich verarbeitet wurde, 0 wenn keine Nachricht zu verarbeiten war
     */
    protected /*abstractImpl*/ function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        Logger::Info("NeuKursFilter {$this->getName()}: Verarbeite Nachricht {$message->getId()}\n");

        $Felder = ['Call', 'DOK', 'OV', 'KursArt', 'KursStatus', /* 'KursText' ist schon im normalen Text drin */]; // diese Felder sind obligatorisch
        $result = count(array_intersect($Felder, array_keys($message->getMetadata()))) == count($Felder);

        // Setze Nachricht auf das Ergebnis der Feldprüfung
        $this->setMessageProcessed($message, $result);

        return 1; // eine Nachricht verarbeitet
    }
}
