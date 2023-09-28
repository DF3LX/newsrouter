<?php

namespace DARCNews\Filter;
use DARCNews\Core\Logger;

/**
 * Dieser Filter reicht eine Nachricht einfach nur weiter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class GenericFilter extends FilterBase
{
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return "Der GenericNewsFilter enthält keine Prüfungen. Er leitet eine Nachricht einfach weiter.";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * Da der Filter nix macht, kann er immer aktiviert werden
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return true;
    }
    
    /**
     * Filterspezifische implementierung. Sagt einfach nur "ja"
     * @return int  Anzahl der verarbeiteten Nachrichten
     */
    protected /*abstractImpl*/ function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0;

        Logger::Info("GenericNewsFilter {$this->getName()}: Verarbeite Nachricht {$message->getId()}\n");

        // Generischer durchleitfilter ist immer erfolgreich
        $this->setMessageProcessed($message, true);

        return 1;   // eine Nachricht verarbeitet
    }
}