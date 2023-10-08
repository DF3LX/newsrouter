<?php

namespace DARCNews\Channel;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;

/**
 * Summary of LogfileChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class LogfileChannel extends ChannelBase
{
    /** @var string PARAM_FILENAME Konstante für Zugriff auf Parameters-Array  */
    private const PARAM_FILENAME = 'Filename';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_FILENAME => [
            'Value' => "logfile.log",
            'Description' => "Pfad und Dateiname der Logdatei, in die geschrieben wird",
            'MultiValue' => false
        ],
    ];

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Ich bin der Channel, der in deine Datei schreibt";
    }

    /**
     * Prüft, ob der Channel aktiviert werden kann.
     * @return bool Flag, ob der Channel aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return !empty($this->getParameter(self::PARAM_FILENAME));
    }

    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0; // 0 Nachrichten verarbeitet

        Logger::Info(static::class . " ({$this->getName()}): verarbeitet Nachricht {$message->getId()}");
        if ($this->isCatchUp()) // bei Catchup machen wir mit der Nachricht einfach mal nix
        {
            $this->setMessageProcessed($message, true, null);
            return 1; // eine Nachricht "verarbeitet"
        }

        // Zero-Width-Space können wir nicht loggen, also ersetzen durch |
        $text = str_replace("\x20\x0B", "|", $message->getText());

        try
        {
            file_put_contents($this->getParameter(self::PARAM_FILENAME),
                "Nachricht {$message->getId()} mit Sequenznummer {$message->getSequence()} erzeugt am {$message->getCreatedAt()->format("Y-M-d H:i:s")}\n"
                . "\tTitel: {$message->getTitel()}\n"
                . "\tTeaser: {$message->getTeaser()}\n"
                . "\tPermalink: {$message->getPermalink()}\n"
                . "\tText: {$text}\n"
                . "\tBildgröße: " . fstat($message->getImage())['size'] . "\n"
                . "\tMetadaten: {$message->getMetadataString()} \n\n",
                FILE_APPEND);
            if ($message->hasImage())
            {
                // Bilddatei mit korrekter Erweiterung rausschreiben
                file_put_contents(dirname($this->getParameter(self::PARAM_FILENAME)) . DIRECTORY_SEPARATOR . 'Image-' . $message->getId() . $message->getExtension(), $message->getImage());
            }
            $message->setUniqueId(uniqid());
            $this->setMessageProcessed($message, true, null);

            return 1; // eine Nachricht verarbeitet
        }
        catch (\Exception $ex)
        {
            $this->setMessageProcessed($message, false, null);
            Logger::Error(static::class . " ({$this->getName()}): Fehler beim Versand von Nachricht {$message->getId()} über Channel {$this->getId()}\n");
            Logger::Error($ex->getMessage());

            return ErrorCodes::Operation_Failed;
        }
    }
}