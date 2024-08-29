<?php

namespace DARCNews\Channel;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;

/**
 * Summary of TelegramChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class TelegramChannel extends ChannelBase
{
    /** @var string PARAM_TOKEN  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_TOKEN = 'Token';

    /** @var string PARAM_BOTUSERNAME  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_BOTUSERNAME = 'BotUsername';

    /** @var string PARAM_CHATID  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_CHATID = 'ChatId';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [
        self::PARAM_TOKEN => [
            'Description' => "Der Bot-Token, wie er vom Botfather ausgegeben wurde",
            'MultiValue' => false,
            'Value' => "",
            'Encrypt' => true
        ],
        self::PARAM_BOTUSERNAME => [
            'Description' => "Username des Bots in Telegram zur Info",
            'MultiValue' => false,
            'Value' => "",
        ],
        self::PARAM_CHATID => [
            'Description' => "ID des Chats, in die der Bot posten soll. Nach dem Einladen des Bots in den Kanal, die URL https://api.telegram.org/bot{Token}/getUpdates aufrufen und nach der Chat-ID suchen",
            'MultiValue' => false,
            'Value' => "",
        ],
    ];

    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Dieser Channel kann eine Nachricht über einen Telegram-Bot schicken, sofern dieser vorher angelegt und konfiguriert wurde";
    }

    /**
     * Prüft, ob der Channel aktiviert werden kann.
     * @return bool Flag, ob der Channel aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return !empty($this->getParameter(self::PARAM_TOKEN))
            && !empty($this->getParameter(self::PARAM_CHATID));
    }
    /**
     * Formatieren der Nachricht und absenden über den Telegram-Bot
     * @return int
     */
    protected /*abstractImpl*/function doStuff(): int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0; // 0 Nachrichten verarbeitet

        try
        {
            $url = 'https://api.telegram.org/bot' . $this->getParameter(self::PARAM_TOKEN) . "/sendMessage";

            $options = array(
                'http' => array(
                    'protocol_version' => '1.1',
                    'method' => 'POST',
                    'header' => array(
                        'Content-Type: application/json; charset=utf-8',
                        'Accept: application/json',
                        'User-Agent: DARC NewsRouter TelegramChannel v1.0',
                    ),
                    'content' => json_encode(
                        [
                            'chat_id' => intval($this->getParameter(self::PARAM_CHATID)),
                            'parse_mode' => 'HTML',
                            'text' =>
                                "<b>" . $message->getTitel() . "</b>\n"
                                . "<i>" . $message->getTeaser() . "</i>\n"
                                . $message->getText()
                        ]
                    )
                )
            );

            $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

            if ($resultText === false)
            {
                throw new \ErrorException("Fehler beim Aufruf der Telegram-API: {$http_response_header[0]}");
            }

            if ($message->hasImage() && $message->getMimeType() == 'image/jpeg')
            {
                $url = 'https://api.telegram.org/bot' . $this->getParameter(self::PARAM_TOKEN) . "/sendPhoto";
                $photo = $message->getImage();
                $tmph = tmpfile();
                fwrite($tmph, $photo);
                $tmpf = stream_get_meta_data($tmph)['uri'];

                $options = array(
                    'http' => array(
                        'protocol_version' => '1.1',
                        'method' => 'POST',
                        'header' => array(
                            'Content-Type: multipart/form-data; charset=utf-8',
                            'Accept: application/json',
                            'User-Agent: DARC NewsRouter TelegramChannel v1.0',
                        ),
                        'content' => json_encode(
                            [
                                'chat_id' => intval($this->getParameter(self::PARAM_CHATID)),
                                'photo' => curl_file_create($tmpf),
                            ]
                        )
                    )
                );

                $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

                if ($resultText === false)
                {
                    throw new \ErrorException("Fehler beim Aufruf der Telegram-API: {$http_response_header[0]}");
                }
            }

            //Logger::Debug($resultText);
            $result = json_decode($resultText, true); // decode JSON
            $uniqueId = $result['result']['message_id'];
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