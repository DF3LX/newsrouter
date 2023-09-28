<?php

namespace DARCNews\Formatter;

use DARCNews\Core\Logger;
use DARCNews\Core\ErrorCodes;

/**
 * Summary of TwitterFormatter
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class TwitterFormatter extends FormatterBase
{
    /** @var string PARAM_MAXCHARACTERS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_MAXCHARACTERS = 'MaxCharacters';
    
    /** @var string PARAM_ERSETZEN Konstante für Zugriff auf Parameters-Array */
    private const PARAM_ERSETZEN = 'Ersetzen';
    
    /** @var string PARAM_HASHTAGS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_HASHTAGS = 'Hashtags';
    
    /** @var string PRAM_MENTIONS Konstante für Zugriff auf Parameters-Array */
    private const PRAM_MENTIONS = 'Mentions';


    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_MAXCHARACTERS => [
            'Description' => "Maximale Anzahl an Zeichen",
            'MultiValue' => false,
            'Value' => 480,
        ],

        self::PARAM_ERSETZEN => [
            'Description' => "Texte, die zur Zeichen-Einsparung ersetzt werden sollen. Format:  <exakt|start|teil> = <Suchtext> = <Ersetzungstext>",
            'MultiValue' => true,
            'Value' => [
                "exakt = Funktionsträgerseminar = FT-Seminar",
                "exakt = Deutscher Amateur-Radio-Club = @DARC_eV",
                "exakt = Distriktsvorsitzender = DV",
                "exakt = Distriktsvorsitzenden = DV",
                "exakt = Distriktsvorsitzende = DV",
                "exakt = Ortsverbandsvorsitzender = OVV",
                "exakt = Ortsverbandsvorsitzenden = OVV",
                "exakt = Ortsverbandsvorsitzende = OVV",
                "exakt = Ortsverband = OV",
                "exakt = Ortsverbände = OVe",
                "exakt = Montag = Mo",
                "exakt = Dienstag = Di",
                "exakt = Mittwoch = Mi",
                "exakt = Donnerstag = Do",
                "exakt = Freitag = Fr",
                "exakt = Samstag = Sa",
                "exakt = Sonntag = So",
                "exakt = Januar = Jan.",
                "exakt = Februar = Feb.",
                "exakt = März = März", // 1:1, lohnt nicht
                "exakt = April = Apr.",
                "exakt = Mai = Mai", // 1:1, lohnt nicht
                "exakt = Juni = Juni", // 1:1, lohnt nicht
                "exakt = Juli = Juli", // 1:1, lohnt nicht
                "exakt = August = Aug.",
                "exakt = September = Sep.",
                "exakt = Oktober = Okt.",
                "exakt = November = Nov.",
                "exakt = Dezember = Dez.",
                //              "start = eins = 1", // lieber nicht, killt so sachen wie "eins-chliesslich"
                "start = zwei = 2",
                "start = drei = 3",
                "start = vier = 4",
                "start = fünf = 5",
                "start = sechs = 6",
                "start = sieben = 7",
                "start = acht = 8",
                "start = neun = 9",
                "start = zehn = 10",
                "start = elf = 11",
                "start = zwölf = 12",
                "exakt = Software Defined Radio = SDR",
                "exakt = Bundesnetzagentur = BnetzA",
                "exakt = Deutschland = DL",
                "exakt = DL3MBG = @DL3MBG",
                "exakt = DG2RON = @DG2RON",
                "exakt = DJ2ET = @DJ2ET",
                "exakt = DF5JL = @DF5JL",
            ],

        ],

        self::PARAM_HASHTAGS => [
            'Description' => "Hashtags im format <start|exakt>=Text",
            'MultiValue' => true,
            'Value' => [
                // Generell
                "start = Amateurfunkpeilen",
                "start = Amateurfunk",
                "start = Funkamateure",
                "start = Funkamateur",
                "start = Ehrenamt",
                "start = Ausbildung",
                // Funkbetrieb
                "start = Hamgroup",
                "start = Clubstation",
                "start = DXpedition",
                "exakt = SOTA",
                "start = HamNet",
                // Referate
                "exakt = AJW",
                "exakt = Bandwacht",
                "exakt = ARDF",
                "exakt = EMV",
                "exakt = Notfunk",
                "exakt = VUS",
                // Conteste
                "exakt = CQWW",
                "exakt = CQWWCW",
                "exakt = CQWWSSB",
                "exakt = CQWPX",
                "exakt = WAG",
                // Organisationen
                "exakt = DARC",
                "exakt = IARU",
                "exakt = YOTA",
                "exakt = AGCW",
                "exakt = ARISS",
                "exakt = AATIS",
                "exakt = ARDC",
                "exakt = NASA",
                "exakt = ÖVSV",
                "exakt = AMSAT",
                // Veranstaltungen
                "start = AfuBarCamp",
                "start = Fieldday",
                "exakt = Clubmeisterschaft",
            ],
        ],

        self::PRAM_MENTIONS => [
            'Description' => "Mentions die eingefügt werden sollen, wenn Text gefunden wird.\nFormat: <genau|start|teil>=<Suchtext>=<@Username>",
            'MultiValue' => true,
            'Value' => [
                // Oranisationen
                "exakt = YOTA = @hamyota",
                "exakt = IARU = @IARU_R1",
                // REferate
                "exakt = AfuBarCamp = @DARC_AJW",
                "exakt = AJW = @DARC_AJW",
                "exakt = treff.darc.de = @DARC_AJW",
                "exakt = Notfunk = @DARC_Notfunk",
                "exakt = Bandwacht = @DL3RTL",
                // Distrikte   
                "exakt = Distrikt Niedersachsen = @darc_DistriktH",
                // Personen
                "exakt = KI5KFH = @astro_matthias",
                "exakt = DL3MBG = @DL3MBG",
                "exakt = DG2RON = @DG2RON",
                "exakt = DJ2ET = @DJ2ET",
                "exakt = DL5XL = @Felix_CUX",
                // OVe
                "exakt = H60 = @DARC_H60",
                "exakt = G09 = @OV_G09",
                "exakt = O33 = @DARC_O33",
                "exakt = P02 = @DARC_P02",
                "exakt = O30 = @DARC_O30",
                "exakt = P31 = @OvP31",
                "exakt = E09 = @ov_e09",
                "exakt = I23 = @darcovi31",
                "exakt = F19 = @DARC_OV_F19",
                "exakt = H13 = @DARC_H13",
                "exakt = H24 = @ovh24",
                "exakt = H42 = @DARC_OV_H42",
                "exakt = G20 = @DB0XO",
                "exakt = I01 = @DARC_OV_I01",
                "exakt = F57 = @DB0FM",
                "exakt = P56 = @taubertalmitte",
                "exakt = K26 = @DARC_K26",
                "exakt = A02 = @darc_a22",
                "exakt = I20 = @i20_vechta",
                "exakt = F11 = @DARC_fox11",
                "exakt = M03 = @DARCM03",
                "exakt = I14 = @DARC_i14",
                "exakt = K14 = @darc_k14",
                "exakt = H26 = @darc_h36",
                "exakt = N41 = @darc_n41",
                "exakt = H33 = @darc_H33",
                "exakt = N47 = @darc_n47",
                "exakt = D23 = @DARC_D23",
                "exakt = O28 = @DARC_O28",
                "exakt = B38 = @DARC_B37",
                "exakt = H21 = @darc_h21",
            ],

        ],
    ];
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Ich schneide Nachrichten bei 480 Zeichen ab, höhö";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return true;
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

        $meldung = new \DOMDocument();
        $meldung->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($message->getText(), 'HTML-ENTITIES', 'UTF-8'));

        // Das eingebettete HTML in dem RSS Feed ist gruselig, also erstmal ein wenig aufräumen
        $MeldungText = trim($message->getTitel()) . ": " . trim(str_replace("\n ", "\n", preg_replace('/(\t+)|(\ )+/', ' ', $meldung->textContent)));

        $MeldungText = $this->processErsetzen($MeldungText);
        $MeldungText = $this->processHashtags($MeldungText);

        $message->setTitel("") // Twitter hat keinen Title
            ->setTeaser("") // Twitter hat keinen Teaser
            ->setText($MeldungText)
            ->setMetadata(self::PRAM_MENTIONS, $this->processMentions($MeldungText)
            );

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

    /**
     * Führt Wort-Ersetzungen durch um den Text zu kürzen
     * @param string $Text  Text, in dem Worte ersetzt werden sollen
     * @return string       Angepasster Text
     */
    protected function processErsetzen(string $Text): string
    {
        foreach ($this->getParameter('Ersetzen') as $line)
        {
            Logger::Debug("Verarbeite Erstzungsregel: {$line}");
            list($match, $alt, $neu) = preg_split('/\\s*[=]\\s*/', $line);

            $Text = match ($match)
            {
                "exakt" => preg_replace("/(\b)(" . preg_quote($alt, '/') . ")(\W)/u", '${1}' . $neu . '${3}', $Text), // PCRE-Modifier (u)nicode
                "start" => preg_replace("/(\b)(" . preg_quote($alt, '/') . ")/u", '${1}' . $neu, $Text), // PCRE-Modifier (u)nicode
                "teil" => preg_replace("/(" . preg_quote($alt, '/') . ")/u", $neu, $Text), // PCRE-Modifier (u)nicode
                default => $Text, // wenn der Match-Operator falsch geschrieben ist
            };
        }
        return $Text;
    }

    /**
     * Sucht nach Hashtags und modifiziert den Text
     * @param string $Text  Text, der mit Hashtags versehen werden soll
     * @return string       Ergebnistext
     */
    protected function processHashtags(string $Text): string
    {
        //Meldungstext mit Hashtags versehen, maximal eine Hash'isierung pro Hashtag
        foreach ($this->getParameter(self::PARAM_HASHTAGS) as $line)
        {
            Logger::Debug("Verarbeite Hashtag: {$line}");
            list($match, $hashtag) = preg_split('/\\s*[=]\\s*/', $line);

            $Text = match ($match)
            {
                "exakt" => preg_replace("/(^| |\"|\()(" . preg_quote($hashtag, '/') . "\W)/ui", "$1#$2", $Text, 1), // PCRE-Modifier (u)nicode, case (i)nsensitive
                "start" => preg_replace("/(^| |\"|\()(" . preg_quote($hashtag, '/') . ")/ui", "$1#$2\x20\x0B", $Text, 1), // PCRE-Modifier (u)nicode, case (i)nsensitive
                "teil" => preg_replace("/(" . preg_quote($hashtag, '/') . ")/ui", "#$2\x20\x0B", $Text, 1), // PCRE-Modifier (u)nicode, case (i)nsensitive
                default => $Text, // wenn der Match-Operator falsch geschrieben ist
            };
            // Entferne das No-Width-Space bei Teilstring-Ersetzung am Ende eines Worts
            if (($match == "teil") || ($match == "start"))
                $Text = str_replace("\x20\x0B ", " ", $Text);
        }
        return $Text;
    }


    /**
     * Prüft den Text auf vorhandene Mentions-Keywords und passt die übergebenene Metadatan an
     * @param string $Text      Text der auf Mentions-Keywords gescannt werden soll
     * @return array            Array mit Mentions
     */
    protected function processMentions(string $Text): array
    {
        $Mentions = array();
        foreach ($this->getParameter(self::PRAM_MENTIONS) as $line)
        {
            Logger::Debug("Verarbeite Mention-Regel: {$line}");
            list($match, $text, $account) = preg_split('/\\s*[=]\\s*/', $line);
            $found = match ($match)
            {
                "exakt" => preg_match("/(\b)(" . preg_quote($text, '/') . ")(\W)/u", $Text),
                "start" => preg_match("/(\b)(" . preg_quote($text, '/') . ")/u", $Text),
                "teil" => preg_match("/(" . preg_quote($text, '/') . ")/u", $Text),
                default => false, // wenn der Match-Operator falsch geschrieben ist
            };
            if ($found)
                $Mentions[] = $account;

        }

        return $Mentions;
    }

    /**
     * Schneidet den Text sinnvoll bei einer bestimmten Länge ab
     * @param string $Text  Der zu verarbeitende Text
     * @return string       Der an der Wortgrenze gekürzte Text
     */
    protected function processMaxLength(string $Text): string
    {
        if (($maxChar = $this->getParameter(self::PARAM_MAXCHARACTERS)) > 0)
        {
            // Meldung auf Maximallänge abschneiden und auf Wortgrenze kürzen
            $Text = substr($Text, 0, $maxChar);
            $Text = substr($Text, 0, strrpos($Text, ' ', -1));
            $Text .= "…";
            Logger::Info("Meldungstext gekürzt auf {$maxChar} Zeichen\n");
        }

        return $Text;
    }
}