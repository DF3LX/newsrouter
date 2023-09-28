<?php

namespace DARCNews\Channel;

/**
 * Summary of FacebookChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class FacebookChannel extends ChannelBase
{
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
    */
    public static /*abstactImpl*/ function getDescription() : string
    {
        return "Ich bin der Facebook Channel";
    }
    /**
     * Prüft, ob der Channel aktiviert werden kann.
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
    protected /*abstractImpl*/ function doStuff() : int
    {
        return 0;
    }
}

