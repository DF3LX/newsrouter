<?php

namespace DARCNews\Core;

/**
 * Die News sind die Basisklasse 
 * @see SourceBase
 * @see FilterBase
 * @see FormatterBase
 * @see ChannelBase
 * 
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 */
class News
{
    private int $id;
    private \DateTime $createdat;
    private int $sourceid;
    private ?string $uniqueid;
    private int $state;

    private ?string $titel;
    private ?string $teaser;
    private string $text;
    private ?string $permalink;
    private mixed $image;
    private ?array $metadata;

    // Felder für output
    private int $formatterid;
    private int $channelid;
    private int $sequence;

    private function __construct()
    {
        $this->id = 0;
        $this->createdat = new \DateTime(); // now
        $this->state = MessageState::Neu;
        $this->sequence = 0;
    }

    public static function CreateFromDB(array $Row): self
    {
        $obj = new self();
        $obj->id = $Row['id'];
        $obj->sourceid = $Row['sourceid'];
        $obj->uniqueid = $Row['uniqueid'];
        $obj->state = $Row['state'];
        $obj->titel = $Row['titel'];
        $obj->teaser = $Row['teaser'];
        $obj->text = $Row['text'];
        $obj->permalink = $Row['permalink'];
        $obj->image = $Row['image'];
        if (!empty($Row['metadata']))
            $obj->metadata = json_decode($Row['metadata'], true);

        if (array_key_exists('formatterid', $Row))
        {
            $obj->formatterid = $Row['formatterid'];
            $obj->channelid = $Row['channelid'];
            $obj->sequence = $Row['sequence'];
        }

        return $obj;
    }

    /**
     * Erzeugt eine Neue Nachricht und befüllt alle Nachrichtenfelder
     * @param string $UniqueId          Unique-Id im Zielsystem
     * @param \DateTime $CreatedAt      Zeitpunkt der erstellung
     * @param mixed $Titel              Titel der Nachricht
     * @param mixed $Teaser             Teasertext
     * @param string $Text              Nachrichtentext
     * @param mixed $Permalink          Permalink zur Nachricht
     * @param mixed $Image              Teaserbild
     * @param mixed $Metadata           Metadaten
     * @return \DARCNews\Core\News      Die Nachricht
     */
    public static function CreateNew(string $UniqueId, \DateTime $CreatedAt, ?string $Titel, ?string $Teaser, string $Text, ?string $Permalink, ?string $Image, ?array $Metadata): self
    {
        $obj = new self();

        $obj->setUniqueId($UniqueId);
        $obj->created = $CreatedAt;
        $obj->setTitel($Titel);
        $obj->setTeaser($Teaser);
        $obj->setText($Text);
        $obj->setPermalink($Permalink);
        $obj->setImage($Image);
        $obj->metadata = $Metadata;

        return $obj;
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdat;
    }

    /**
     * @return int
     */
    public function getSourceId(): int
    {
        return $this->sourceid;
    }

    /**
     * @param int $sourceid 
     * @return self
     */
    public function setSourceId(int $SourceId): self
    {
        $this->sourceid = $SourceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(): ?string
    {
        return $this->uniqueid;
    }

    /**
     * @param string $UniqueId 
     * @return self
     */
    public function setUniqueId(string $UniqueId): self
    {
        $this->uniqueid = $UniqueId;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return ?string
     */
    public function getTitel(): ?string
    {
        return $this->titel;
    }

    /**
     * @param string $titel 
     * @return self
     */
    public function setTitel(?string $Titel): self
    {
        if (($Titel != null) && !mb_check_encoding($Titel, 'UTF-8'))
        {
            Logger::Debug("News: Konvertiere Titel nach UTF-8: " . $Titel);
            $Titel = mb_convert_encoding($Titel, 'UTF-8');
        }
        $this->titel = $Titel;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser 
     * @return self
     */
    public function setTeaser(?string $Teaser): self
    {
        if (($Teaser != null) && !mb_check_encoding($Teaser, 'UTF-8'))
        {
            Logger::Debug("News: Konvertiere Teaser nach UTF-8: " . $Teaser);
            $Teaser = mb_convert_encoding($Teaser, 'UTF-8');
        }
        $this->teaser = $Teaser;
        return $this;
    }

    /**
     * Summary of hasTeaser
     * @return bool
     */
    public function hasTeaser(): bool
    {
        return !empty($this->teaser);
    }
    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function getTextAsText(): string
    {
        $dom = $this->getTextasDOM();
        $this->CleanTable($dom);

        return $dom->textContent;
    }

    public function getTextAsHTML(): string
    {
        $dom = $this->getTextasDOM();
        $xp = new \DOMXPath($dom);

        $body = $xp->query("//body")[0]; // Uns interessiert nur das html ab Body. Da es kein "innerHTML" gibt, müssen wir alle childs einzeln speichern
        $html = ""; // init für append
        foreach ($body->childNodes as $child)
            $html .= $dom->saveHTML($child);

        return $html;
    }

    public function getTextasDOM(): \DOMDocument
    {
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $this->text); // in der DB steht UTF-8  Text

        return $dom;
    }
    /**
     * @param string $Text 
     * @return self
     */
    public function setText(string $Text): self
    {
        if (($Text != null) && !mb_check_encoding($Text, 'UTF-8'))
        {
            Logger::Debug("News: Konvertiere Text nach UTF-8: " . $Text);
            $Text = mb_convert_encoding($Text, 'UTF-8');
        }
        $this->text = $Text;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getPermalink(): ?string
    {
        return $this->permalink;
    }

    /**
     * @param string $Permalink 
     * @return self
     */
    public function setPermalink(string $Permalink): self
    {
        $this->permalink = $Permalink;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage(): mixed
    {
        return $this->image;
    }

    public function getImageData() : string
    {
        $data = stream_get_contents($this->image);
        fseek($this->image, 0);
        return $data;
    }

    /**
     * @param mixed $Image 
     * @return self
     */
    public function setImage(mixed $Image): self
    {
        $this->image = $Image;
        return $this;
    }

    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata ?? [];
    }

    public function getMetadataString(): string
    {
        return json_encode($this->getMetadata());
    }

    public function setMetadata(string $Key, mixed $Value): self
    {
        if (!isset($this->metadata))
            $this->metadata = [];

        $this->metadata[$Key] = $Value;

        return $this;
    }

    /**
     * @param int $Sequence 
     * @return self
     */
    public function setSequence(int $Sequence): self
    {
        $this->sequence = $Sequence;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /* Tools und Helper-Funktionen */

    public function getMimeType(): string
    {
        if (empty($this->image))
            return "";

        $check = stream_get_contents($this->image, 4);
        fseek($this->image, 0); // Unbedingt wieder zurückspulen

        if ($check == "\xff\xd8\xff\xE0")
            return "image/jpeg";

        if ($check == "\x89\x50\x4E\x47")
            return 'image/png';

        if ($check == "\x47\x49\x46\x38")
            return 'image/gif';

        if (substr($check, 0, 2) == "\x42\4d")
            return 'image/bitmap';

        return 'application/octet-stream'; // generic binary
    }

    public function getExtension(): string
    {
        return match ($this->getMimeType())
        {
            'image/jpeg' => ".jpg",
            'image/png' => ".png",
            'image/gif' => ".gif",
            'image/bitmap' => ".bmp",
            '' => "",
            default => ".bin",
        };
    }

    private static function CleanTable(\DOMDocument &$Dom): void
    {
        $q = new \DOMXPath($Dom);

        // Lösche leere Text-Knoten innerhalb der Table-Struktur
        // textContent gibt die sonst nämlich aus :-(
        foreach ($q->query("//table//text()") as $node)
        {
            if (empty(trim($node->nodeValue)))
            {
                $node->parentNode->removeChild($node);
            }
        }

        // Füge Zeilenumbruch an Zellen-Wert an
        foreach ($q->query("//td|th") as $td)
        {
            $td->nodeValue = trim($td->nodeValue) . "\n";

            // Wenn letzte Zelle, dann füge noch einen Zeilenumbruch hinzu
            if ($td->nextElementSibling == null)
            {
                $td->nodeValue .= "\n";
            }
        }
    }
}