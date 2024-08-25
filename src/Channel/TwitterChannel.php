<?php

namespace DARCNews\Channel;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;

/**
 * Summary of TwitterChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @author Felix, DF3LX <df3lx@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
*/
class TwitterChannel extends ChannelBase
{
    /** @var string PARAM_GPSLON  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_GPSLON = 'GPSLon';
    
    /** @var string PARAM_GPSLAT  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_GPSLAT =  'GPSLat';
    
    /** @var string PARAM_BEARERTOKEN  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_BEARERTOKEN =  'oauth_token';

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters =
        [
           self::PARAM_GPSLON => [
                'Description' => "GPS-Koordinaten Longitude",
                'MultiValue' => false,
                'Value' => "51.2668582",
            ],

           self::PARAM_GPSLAT => [
                'Value' => "9.42681590",
                'Description' => "GPS-Koordinaten Latitude",
                'MultiValue' => false
            ],

            self::PARAM_BEARERTOKEN => [
                'Description' => "Das Twitter Bearer-Token",
                'MultiValue' => false,
                'Value' => '',
            ],
        ];
        
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
    */
    public static /*abstractImpl*/ function getDescription() : string
    {
        return "Dieser Channel kann einen Tweet bei X, ehemals Twitter absetzen.";
    }

    /**
     * Prüft, ob der Channel aktiviert werden kann.
     * @return bool Flag, ob der Channel aktiviert werden kann.
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return !empty($this->getParameter(self::PARAM_BEARERTOKEN));
    }
    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/ function doStuff() : int
    {
        $message = $this->getUnprocessedMessage();

        if ($message == null)
            return 0; // 0 Nachrichten verarbeitet

        try  {
            // Hochladen möglicher Bilder

            if($message->hasImage())
            {

                $options = array(
                    'http' => array(
                        'protocol_version' => '1.1',
                        'method' => 'POST',
                        'header' => array(
                            'Content-Type: multipart/form-data; charset=utf-8',
                            'Accept: application/json',
                            'Authorization: Bearer ' . self::PARAM_BEARERTOKEN,
                            'User-Agent: DARC NewsRouter TwitterChannel v1.0',
                        ),
                        'content' => json_encode(
                            [
                                'media_data' => base64_encode($message->getImage()),
                            ]
                        )
                    )
                );

                $url = 'https://upload.twitter.com/1.1/media/upload.json';
                $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

                if ($resultText === false)
                {
                    throw new \ErrorException("Fehler beim Hochladen des Bildes: {$http_response_header[0]}");
                }

                $result = json_decode($resultText, true); // decode JSON
                $mediaId = $result['media_id'];

                $options = array(
                    'http' => array(
                        'protocol_version' => '1.1',
                        'method' => 'POST',
                        'header' => array(
                            'Content-Type: application/json; charset=utf-8',
                            'Accept: application/json',
                            'Authorization: Bearer ' . self::PARAM_BEARERTOKEN,
                            'User-Agent: DARC NewsRouter TwitterChannel v1.0',
                        ),
                        'content' => json_encode(
                            [
                                'text' => $message->getText(),
                                'media_ids' => $mediaId,
                            ]
                        )
                    )
                );
            }
            else
            {
                $options = array(
                'http' => array(
                    'protocol_version' => '1.1',
                    'method' => 'POST',
                    'header' => array(
                        'Content-Type: application/json; charset=utf-8',
                        'Accept: application/json',
                        'Authorization: Bearer ' . self::PARAM_BEARERTOKEN,
                        'User-Agent: DARC NewsRouter TwitterChannel v1.0',
                    ),
                    'content' => json_encode(
                        [
                            'text' => $message->getText(),
                        ]
                    )
                )
            );
            }

            $url = 'https://api.x.com/2/tweets';
            $resultText = file_get_contents($url, false, stream_context_create($options)); // send https request

            if ($resultText === false)
            {
                throw new \ErrorException("Fehler beim Aufruf der Twitter-API: {$http_response_header[0]}");
            }

            $result = json_decode($resultText, true); // decode JSON
            $uniqueId = $result['event_id'];


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
