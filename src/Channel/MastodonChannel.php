<?php

namespace DARCNews\Channel;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;

/**
 * Summary of MastodonChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class MastodonChannel extends ChannelBase
{

    /** @var string PARAM_TOKEN  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TOKEN = 'Token';

    /** @var string PARAM_BOTUSERNAME  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_SERVERBASEURL = 'ServerBaseUrl';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_TOKEN => [
            'Description' => "Der Token, wie er vom Mastodon für diesen Account ausgegeben wurde",
            'MultiValue' => false,
            'Value' => "",
            'Encrypt' => true
        ],
        self::PARAM_SERVERBASEURL => [
            'Description' => "Name des Servers mit https:// davor",
            'MultiValue' => false,
            'Value' => "https://social.darc.de",
        ],
    ];
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Dieser Channel kann Texte und Bilder auf Mastodon posten, vorausgesetzt, ein API Key wurde übergeben";
    }

    /**
     * Prüft, ob der Channel aktiviert werden kann.
     * @return bool Flag, ob der Channel aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return !empty($this->getParameter(self::PARAM_TOKEN))
            && !empty($this->getParameter(self::PARAM_SERVERBASEURL));
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

        try
        {
            $mediaId = null;

            // Wenn ein Bild vorhanden ist, muss zuerst das Bild hochgeladen werden
            if ($message->hasImage())
            {
                $filename = basename(parse_url($message->getMetadata()['imageUrl'] ?? 'unknown.jpg', PHP_URL_PATH));
                $mimetype = $message->getMimeType();

                $delimiter = '-------------' . uniqid();

                $options = array(
                    'http' => array(
                        'header' => array(
                            'Accept: application/json',
                            'User-Agent: DARC NewsRouter MastodonChannel v1.0',
                            "Authorization: Bearer " . $this->getParameter(self::PARAM_TOKEN),
                            "Content-Type: multipart/form-data; boundary=$delimiter",
                        ),
                        'method' => 'POST',
                        'content' => "--$delimiter\r\n"
                            . "Content-Disposition: form-data; name=\"file\"; filename=\"{$filename}\"\r\n"
                            . "Content-Type: {$mimetype}\r\n"
                            . "\r\n"
                            . $message->getImageData()
                            . "\r\n"
                            . "--$delimiter--",
                    ),
                );

                $url = $this->GetParameter(self::PARAM_SERVERBASEURL) . '/api/v2/media';
                $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

                if ($resultText === false)
                {
                    throw new \ErrorException("Fehler beim Aufruf der Mastodon-API für Bildupload: {$http_response_header[0]}");
                }

                $result = json_decode($resultText, true); // decode JSON
                $mediaId = $result['id'];
            }

            // post Status
            $options = array(
                'http' => array(
                    'protocol_version' => '1.1',
                    'method' => 'POST',
                    'header' => array(
                        'Content-Type: application/json; charset=utf-8',
                        'Accept: application/json',
                        'User-Agent: DARC NewsRouter MastodonChannel v1.0',
                    ),
                    'content' => json_encode(
                        [
                            'media_ids' => [$mediaId],
                            'spoiler_text' => $message->getTitel() . ($message->hasTeaser() ? ("\n" . $message->getTeaser() . "\n") : null),
                            'status' => $message->getText(),
                            'visibility' => 'public',
                            'language' => 'de'
                        ]
                    )
                )
            );

            $url = $this->GetParameter(self::PARAM_SERVERBASEURL) . '/api/v1/statuses';
            $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

            if ($resultText === false)
            {
                throw new \ErrorException("Fehler beim Aufruf der Telegram-API: {$http_response_header[0]}");
            }


            $result = json_decode($resultText, true); // decode JSON
            $uniqueId = $result['id'];
            $this->setMessageProcessed($message, true, $uniqueId);

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

