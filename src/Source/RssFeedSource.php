<?php

namespace DARCNews\Source;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;
use DARCNews\Core\News;


/**
 * Liest den DARC-RSS Feed aus. Vielleicht auch andere
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class RssFeedSource extends SourceBase
{
    /** @var string PARAM_MAXCHARACTERS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_PUBLISHWAITSEC = 'PublishWaitSec';

    /** @var string PARAM_MAXCHARACTERS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_URL = 'Url';

    /** @var string PARAM_MAXCHARACTERS Konstante für Zugriff auf Parameters-Array */
    private const PARAM_LASTPUBDATE = 'LastPubDate';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_PUBLISHWAITSEC => [
            'Value' => 600,
            'Description' => "Mindestalter in Sekunden, bevor eine neue Nachricht verarbeitet wird",
            'MultiValue' => false
        ],
        self::PARAM_URL => [
            'Value' => "https://www.darc.de/home/rss.xml",
            'Description' => "URL des RSS-Feeds, der abgefragt werden soll",
            'MultiValue' => false
        ],
        self::PARAM_LASTPUBDATE => [
            'Value' => 0,
            'Description' => "nur Intern: Unix-Timestamp der letzten verarbeitung des Feeds. ",
            'MultiValue' => false,
            'UserSetting' => false // wird noch nicht drauf reagiert. könnte aber
        ],
    ];

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Die RSSFeedSource kann den RSS-Feed der DARC-Webseite auslesen";
    }


    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return (!empty($this->getParameter(self::PARAM_URL)));
    }

    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/function doStuff(): int
    {
        $feed = new \SimpleXMLElement($this->getParameter(self::PARAM_URL), 0, TRUE);

        if (!$feed)
            return -1; // Problem beim einlesen des Feeds;

        Logger::Debug("RSS Feed erfolgreich geladen\n");
        Logger::Debug($feed->channel->pubDate);
        $feedDate = \DateTime::createFromFormat(\DateTime::RFC1123, $feed->channel->pubDate); //("D, d m Y H:i:s O", $feed->channel->pubDate);
        $lastPubDate = new \DateTime();
        $lastPubDate->setTimestamp($this->getParameter(self::PARAM_LASTPUBDATE));

        Logger::Debug("FeedDate: {$feedDate->format(\DateTime::ISO8601)}}, LastPubDate: {$lastPubDate->format(\DateTime::ISO8601)}");

        if (!($feedDate > $lastPubDate))
        {
            Logger::Info(static::class . " ({$this->getName()}): Feed Date ist nicht neuer als beim letzten Mal");
                return 0; // 0 neue Nachrichten
        }

        Logger::Info(static::class . " ({$this->getName()}): Feed ist neu");

        $this->setParameter(self::PARAM_LASTPUBDATE, $feedDate->getTimestamp()); // set last pub date and Save

        $items = $feed->xpath('/rss/channel/item');

        // Prüfung nicht nötig. ein xPath gibt immer mindestens ein leeres Array aus
        //if (!is_array($items))
        //    return 0; // Keine Items im Feed

        // sortieren nach pubDate
        usort($items, function ($a, $b)
        {
            $dateA = \DateTime::createFromFormat(\DateTime::RFC1123, $a->pubDate); // <pubDate>Mon, 23 May 2022 12:52:50 +0200</pubDate>
            $dateB = \DateTime::createFromFormat(\DateTime::RFC1123, $b->pubDate);
            return $dateA > $dateB ? 1 : -1;
        });

        $checkDate = new \DateTime(); // now as comparer
        $itemCount = 0;

        foreach ($items as $item)
        {
            Logger::Debug("Verarbeite ein Item");
            $pubDate = \DateTime::createFromFormat(\DateTime::RFC1123, $item->pubDate);

            if (($checkDate->getTimestamp() - $pubDate->getTimestamp()) < $this->getParameter(self::PARAM_PUBLISHWAITSEC))
            {
                Logger::Info(static::class . " ({$this->getName()}): Newsmeldung {$item->guid} ist noch keine 10 Minuten alt.\n");
                continue; // Skip Items die noch keine 10 Minuten alt sind.
            }

            if ($this->isMessageKnown($item->guid))
            {
                Logger::Info(static::class . " ({$this->getName()}): Newsmeldung {$item->guid} ist bereits abgespeichert");
                continue;
            }

            // Nachrichtencontent verarbeiten
            // Knoten content:encoded abfragen und zum String casten um an den Inhalt zu kommen. 
            $content = (string) $item->children('content', true)->encoded;

            // Der DARC-RSS Feed gibt gruseliges, eingerücktes HTML. Hier ein einfacher Versuch, das sauber zu bekommen
            $content = preg_replace('/\s+/', " ", $content);

            // das halbwegs saubere HTML können wir in einen DOM packen um mit XPath Queries arbeiten zu können.
            // nen SimpleXMLElement wär mir ja lieber, aber der DOM kommt besser mit nicht-well-formed HTML klar

            $dom = new \DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content); // ist ja UTF8, was da ankommt
            $xp = new \DOMXPath($dom);

            // Teaserbild suchen
            $imageUrl = $xp->evaluate("string(//img[1]/@src)"); // Attribut src vom ersten gefundenen IMG tag

            // Beim DARC-RSS ist das Teaserbild in einem DIV. Das kann jetzt weg
            $div = $xp->query("//div[@class='news-img-wrap']")[0];
            $div?->parentNode?->removeChild($div);

            $body = $xp->query("//body")[0]; // Uns interessiert nur das html ab Body. Da es kein "innerHTML" gibt, müssen wir alle childs einzeln speichern
            $meldungHtml = ""; // init für append
            foreach ($body->childNodes as $child)
                $meldungHtml .= $dom->saveHTML($child);

            $imageStream = null; // declare variable
            if (filter_var($imageUrl, FILTER_VALIDATE_URL))
            {
                Logger::Info(static::class . " ({$this->getName()}): Teaserbild in Meldung {$item->guid} gefunden. Versuche Download von {$imageUrl}");
                $imageStream = file_get_contents($imageUrl);
                Logger::Debug("Länge vom Image " . strlen($imageStream));
            }

            $result = $this->saveMessage(
                News::CreateNew(
                    $item->guid,
                    $pubDate,
                    $item->title,
                    null,
                    $meldungHtml,
                    $item->link,
                    $imageStream,
                    ['ImageUrl' => $imageUrl] // wer weiss, ob man die noch mal braucht
                )
            );

            if ($result == ErrorCodes::AlreadyExists)
            {
                Logger::Error(static::class . " ({$this->getName()}): Die Nachricht {$item->guid} ist bereits vorhanden\n");
                continue;
            }
            $itemCount++;
        } // foreach
        return $itemCount;
    } // doStuff
} // class