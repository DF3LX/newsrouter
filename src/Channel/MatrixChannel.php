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
        Logger::Error($message);
        $text = gettext($message);

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