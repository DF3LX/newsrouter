<?php

namespace DARCNews\Channel;

/**
 * Summary of TwitterChannel
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
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
            && !empty($this->getParameter(self::PARAM_CONSUMERSECRET));
    }
    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/ function doStuff() : int
    {
        return 0;
    }
}
