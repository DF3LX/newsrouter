<?php

namespace DARCNews\Channel;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;

/**
 * Summary of MatrixChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class MatrixChannel extends ChannelBase
{
    /**
     * Definition der usage relevanten Nachrichten
     */

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/ function getDescription(): string
    {
        return "Ich bin der Matrix Channel";
    }

    /**
     * Prüft, ob der Filter aktiviert werden kann.
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
    /**
     * Summary of doStuffold
     * @return int
     */
    protected /*abstractImpl*/function doStuffold(): int
    {

        $message = $this->getUnprocessedMessage();
        $text = strval($message->getText());
        Logger::Error($text);

        if ($text === $message) {

            $text = "Translation not available for: " . $message;
        }

        $msgtype = "m.text";
        $homeserver = "matrix.org";
        $room = "!QcJtRjnvlChOaNGOkc:matrix.org";
        $accesstoken = "syt_ZnAtbWFpbA_AMgaqjtFDRcaSBspIiRU_06kNqF";


        $data = [
            "msgtype" => $msgtype,
            "body" => $text
        ];
        $jsonPayload = json_encode($data);

        /*
         * Vorbereitung der Payload
         */

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                    "Content-Length: " . strlen($jsonPayload) . "\r\n",
                'content' => $jsonPayload
            ]
        ];
        $context = stream_context_create($options);



        if ($message == null)
            return 0; // 0 Nachrichten verarbeitet

        Logger::Info("Channel {$this->getName()} verarbeitet Nachricht {$message->getId()}");
        if ($this->isCatchUp()) // bei Catchup machen wir mit der Nachricht einfach mal nix
        {
            $this->setMessageProcessed($message, true, null);
            return 1; // eine Nachricht "verarbeitet"
        }


        try
        {
            $response = file_get_contents("https://$homeserver/_matrix/client/r0/rooms/$room/send/m.room.message?access_token=$accesstoken", false, $context);

            $this->setMessageProcessed($message, true, null);

            return 1; // eine Nachricht verarbeitet
        }
        catch (\Exception $ex)
        {
            $this->setMessageProcessed($message, false, null);
            Logger::Error("Fehler beim Versand von Nachricht {$message->getId()} über Channel {$this->getId()}\n");
            Logger::Error($ex->getMessage());

            return ErrorCodes::Operation_Failed;
        }

    }
}