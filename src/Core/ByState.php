<?php

namespace DARCNews\Core;

/**
 * ENUM Ersatz für die Werte aus der Tabelle _byState für die Flags in den Tabellen
 * FilteredBy und ProcessedBy
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class ByState
{
    /** @var int Error  Fehlerstatus */
    public const Error = -1;
    /** @var int NoMatch    Kein Filter-Treffer  */
    public const NoMatch = 0;
    /** @var int Successful Erfolgreich verarbeitet bzw. Filter hat gemacht */
    public const Successful = 1;
    /** @var int CatchUp Durch CatchUp Modus verarbeitet */
    public const CatchUp = 2;
}