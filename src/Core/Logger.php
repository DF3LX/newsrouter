
<?php

namespace DARCNews\Core;

/**
 * Einfache Logger-Klasse
 * * Aktuell noch Konsolenausgabe, wird vielleicht mal ne Logdatei
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class Logger
{
    public const LOG_NONE = 99;
    public const LOG_DEBUG = 1;
    public const LOG_INFO = 2;
    public const LOG_WARN = 3;
    public const LOG_ERROR = 4;

    /** @var int $_loglevel hält den LogLeven */
    private static int $_loglevel = 1;

    /**
     * Setzt de gewünschten LogLevel
     * @param int $LogLevel gewünschter LogLevel
     * @return void         nix, gar inx
     */
    public static function setLogLevel(int $LogLevel): void
    {
        self::$_loglevel = $LogLevel;
    }
    /**
     * Loggt einen Debug-Text, wenn das LogLevel passt
     * @param string $Text  Logtext
     * @return void         nix, gar inx
     */
    public static function Debug(string $Text): void
    {
        if (self::$_loglevel <= self::LOG_DEBUG)
            self::doLog(\CliColor::WHITE, $Text);
    }

    /**
     * Loggt einen Info-Text, wenn das LogLevel passt
     * @param string $Text  Logtext
     * @return void         nix, gar inx
     */
    public static function Info(string $Text): void
    {
        if (self::$_loglevel <= self::LOG_INFO)
            self::doLog(\CliColor::GRAY, $Text);
    }

    /**
     * Loggt einen Warn-Text, wenn das LogLevel passt
     * @param string $Text  Logtext
     * @return void         nix, gar inx
     */
    public static function Warn(string $Text): void
    {
        if (self::$_loglevel <= self::LOG_WARN)
            self::doLog(\CliColor::YELLOW, $Text);
    }

    /**
     * Loggt einen Error-Text, wenn das LogLevel passt
     * @param string $Text  Logtext
     * @return void         nix, gar inx
     */
    public static function Error(string $Text): void
    {
        if (self::$_loglevel <= self::LOG_ERROR)
            self::doLog(\CliColor::RED, $Text);
    }

    /**
     * Führt die Logausgabe durch
     * @param string $Color Farbe des Logtexts
     * @param string $Text  Logtext
     * @return void         nix, gar inx
     */
    private static function doLog(string $Color, string $Text): void
    {
        \CliColor::echoC($Color, $Text);
        if ($Text[strlen($Text) - 1] != "\n")
            echo "\n";
    }
}