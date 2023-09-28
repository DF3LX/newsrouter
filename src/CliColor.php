<?php

/**
 * Summary of CliColor
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
*/
class CliColor
{
    public const BLACK = "\033[1;30m";
    public const RED = "\033[1;31m";
    public const GREEN = "\033[1;32m";
    public const YELLOW = "\033[1;33m";
    public const BLUE = "\033[1;34m";
    public const MAGENTA = "\033[1;35m";
    public const CYAN = "\033[1;36m";
    public const WHITE = "\033[1;37m";


    public const DARKGRAY = "\033[1;30m";
    public const DARKRED = "\033[0;31m";
    public const DARKGREEN = "\033[0;32m";
    public const DARKYELLOW = "\033[0;33m";
    public const DARKBLUE = "\033[0;34m";
    public const DARKMAGENTA = "\033[0;35m";
    public const DARKCYAN = "\033[0;36m";
    public const DARKWHITE = "\033[0;37m";
    public const GRAY = "\033[0;37m";


    public const BGBLACK = "\033[40m";
    public const BGRED = "\033[41m";
    public const BGGREEN = "\033[42m";
    public const BGYELLOW = "\033[43m";
    public const BGBLUE = "\033[44m";
    public const BGMAGENTA = "\033[45m";
    public const BGCYAN = "\033[46m";
    public const BGWHITE = "\033[47m";

    public const BGLIGHTBLACK = "\033[100m";
    public const BGLIGHTRED = "\033[101m";
    public const BGLIGHTGREEN = "\033[102m";
    public const BGLIGHTYELLOW = "\033[103m";
    public const BGLIGHTBLUE = "\033[104m";
    public const BGLIGHTMAGENTA = "\033[105m";
    public const BGLIGHTCYAN = "\033[106m";
    public const BGLightWHITE = "\033[107m";

    public const B1 = "\033[1m"; // Bold On
    public const B0 = "\033[21m"; // Bold Off
    public const W1 = "\033[2m"; // Weak On
    public const W0 = "\033[22m"; // Weak Off
    public const K1 = "\033[3m"; // Kursiv On (Italics)
    public const K0 = "\033[23m"; // Kursiv Off
    public const U1 = "\033[4m"; // Underline on
    public const U0 = "\033[24m"; // Underline off
    public const I1 = "\033[7m"; // Inverse on
    public const I0 = "\033[27m"; // Inverse off
    public const RESET = "\033[0m";


    /** @var array Tags, die innerhalb eines Textes zu ANSI-Farbcodes ersetzt werden */
    public static $tags = array(
        '|BK|' => self::BLACK,
        '|RD|' => self::RED,
        '|GE|' => self::GREEN,
        '|YE|' => self::YELLOW,
        '|BL|' => self::BLUE,
        '|MA|' => self::MAGENTA,
        '|CY|' => self::CYAN,
        '|WH|' => self::WHITE,

        '|DGR|' => self::DARKGRAY,
        '|DRD|' => self::DARKRED,
        '|DGE|' => self::DARKGREEN,
        '|DYE|' => self::DARKYELLOW,
        '|DBL|' => self::DARKBLUE,
        '|DMA|' => self::DARKMAGENTA,
        '|DCY|' => self::DARKCYAN,
        '|DWH|' => self::DARKWHITE,
        '|GR|' => self::GRAY,

        '|BBK|' => self::BGBLACK,
        '|BRD|' => self::BGRED,
        '|BGE|' => self::BGGREEN,
        '|BYE|' => self::BGYELLOW,
        '|BBL|' => self::BGBLUE,
        '|BMA|' => self::BGMAGENTA,
        '|BCY|' => self::BGCYAN,
        '|BWH|' => self::BGWHITE,

        '|BLBK|' => self::BGLIGHTBLACK,
        '|BLRD|' => self::BGLIGHTRED,
        '|BLGE|' => self::BGLIGHTGREEN,
        '|BLYE|' => self::BGLIGHTYELLOW,
        '|BLBL|' => self::BGLIGHTBLUE,
        '|BLMA|' => self::BGLIGHTMAGENTA,
        '|BLCY|' => self::BGLIGHTCYAN,
        '|BLWH|' => self::BGLightWHITE,

        '|B1|' => self::B1, // Bold On
        '|B0|' => self::B0, // Bold Off
        '|W1|' => self::W1, // Weak On
        '|W0|' => self::W0, // Weak Off
        '|K1|' => self::K1, // Kursiv On (Italics)
        '|K0|' => self::K0, // Kursiv Off
        '|U1|' => self::U1, // Underline on
        '|U0|' => self::U0, // Underline off
        '|I1|' => self::I1, // Inverse on
        '|I0|' => self::I0, // Inverse off
        '|x|' => self::RESET,
    );


    /**
     * Schreibt einen Text in der angegebenen Farbe auf die Konsole und ersezt FarbTags
     * @param mixed $Color  Standardfarbe
     * @param mixed $Text   Text mit Text
     * @return void         nix, gar nix
     */
    public static function echoC($Color, $Text)
    {
        self::echo(str_replace("|u|", $Color, $Color . $Text . "|x|"));
    }
    
    /**
     * Schreibt einen Text auf die Konseole und ersetzt die FarbTags durch ANSI-Colorcodes
     * @param string $Text  Text
     * @return void         Nix, gar nix
     */
    public static function echo (string $Text): void
    {
        echo str_replace(array_keys(static::$tags), static::$tags, $Text. "|x|");
    }

    /**
     * Lichtorgel
     * @return void wirklich nix
     */
    public static function Test()
    {
        /*
           if ($bold == 3)
               continue;
           if ($bold == 4)
               continue;
           if ($bold == 9)
               continue;
           if ($bold == 5)
               continue;
      */
        for ($id = 16; $id < 0x70; $id++)
        {
            if ($id == 48)
                $id = 80;

            // if ($id % 32 == 0)
            // echo self::RESET . "\n";
            for ($bold = 0; $bold < 16; $bold++)
            {

                echo "\033[{$bold};{$id}m" . sprintf("%01d", $bold) . ":" . sprintf("%03d", $id) . " " . self::RESET;

            }
            echo self::RESET . "\n";
        }
        echo self::RESET;
    }

    /**
     * Schreibt einen Text in der Error-Farbe
     * @param string $Text  Text
     * @return void         nix, gar nix
     */
    public static function Error(string $Text): void
    {
        self::echoC("|RD|" , $Text);
    }

    /**
     * Schreibt einen Text in der Warnung-Farbe
     * @param string $Text  Text
     * @return void         nix, gar nix
     */
    public static function Warn(string $Text): void
    {
        self::echoC("|DYE|" , $Text);
    }

    /**
     * Schreibt einen Text in der Erfolg-Farbe
     * @param string $Text  Text
     * @return void         nix, gar nix
     */
    public static function Success(string $Text): void
    {
        self::echoC("|DGE|" , $Text);
    }

    /**
     * Schreibt einen Text in der Highlight-Farbe
     * @param string $Text  Text
     * @return void         nix, gar nix
     */
    public static function Highlight(string $Text): void
    {
        self::echoC("|WH|" , $Text);
    }

    /**
     * Schreibt einen Text in der Standard-Farbe
     * @param string $Text  Text
     * @return void         nix, gar nix
    */
    public static function Print(string $Text): void
    {
        self::echoC("|x|" , $Text);
    }

}