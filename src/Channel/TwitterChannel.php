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
    
    /** @var string PARAM_OAUTHTOKEN  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_OAUTHTOKEN =  'oauth_token';
    
    /** @var string PARAM_OAUTHSECRET  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_OAUTHSECRET =  'oauth_secret';

    /** @var string PARAM_CONSUMERKEY  Konstante für Zugriff auf Parameters-Array */
    private const PARAM_CONSUMERKEY = 'consumer_key';

    /** @var string PARAM_CONSUMERSECRET Konstante für Zugriff auf Parameters-Array */
    private const PARAM_CONSUMERSECRET = 'consumer_secret';

    /** @var string PARAM_SERVERBASEURL Konstante für Zugriff auf Parameters-Array */
    private const PARAM_SERVERBASEURL = 'endpoint_url';

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
                'Description' => "GPS-Koordinaten Latitute",
                'MultiValue' => false
            ],

            self::PARAM_OAUTHTOKEN => [
                'Description' => "Das Twitter OAuth-Token",
                'MultiValue' => false,
                'Value' => '',
            ],

           self::PARAM_OAUTHSECRET => [
                'Description' => "Das Twittre OAuth-Secret",
                'MultiValue' => false,
                'Value' => '',
            ],

           self::PARAM_CONSUMERKEY => [
                'Description' => "Der Consumer-Keyn",
                'MultiValue' => false,
                'Value' => '',
            ],

            self::PARAM_CONSUMERSECRET => [
                'Description' => "Das Consumer-Secret",
                'MultiValue' => false,
                'Value' => '',
            ],
        ];
        
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Channels liefert.
     * @return string   Beschreibung
    */
    public static /*abstactImpl*/ function getDescription() : string
    {
        return "Dieser Channel kann einen Tweet bei X, ehemals Twitter absetzen.";
    }

    /**
     * Prüft, ob der Channel aktiviert werden kann.
     * @return bool Flag, ob der Channel aktiviert werden kann.
     */
    protected /*abstractImpl*/ function canEnable(): bool
    {
        return !empty($this->getParameter(self::PARAM_OAUTHTOKEN))
            && !empty($this->getParameter(self::PARAM_OAUTHSECRET))
            && !empty($this->getParameter(self::PARAM_CONSUMERKEY))
            && !empty($this->getParameter(self::PARAM_CONSUMERSECRET))
            && !empty($this->getParameter(self::PARAM_SERVERBASEURL));
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
                            'Authorization: Bearer ' . self::PARAM_OAUTHTOKEN,
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
                            'Authorization: Bearer ' . self::PARAM_OAUTHTOKEN,
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
                        'Authorization: Bearer ' . self::PARAM_OAUTHTOKEN,
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

            $url = $this->GetParameter(self::PARAM_SERVERBASEURL) . '/2/tweets' ;
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
