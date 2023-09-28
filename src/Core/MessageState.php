<?php

namespace DARCNews\Core;

/**
 * ENUM-Ersatz für MessageStates in der Datenbank
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class MessageState
{
    /** @var int Error  Nachricht fehlerhaft verarbeitet */
    public const Error = -1;
    /** @var int Neu  Nachricht ist neu */
    public const Neu = 0;
    /** @var int Processed  Nachricht korrekt verarbeitet */
    public const Processed = 1;
    /** @var int NurHeader  Nur Nachrichtenheader vorhanden */
    public const NurHeader = 2;
    /** @var int CatchUp  Nur Nachrichtenheader wegen CatchUp */
    public const CatchUp = 3;
}