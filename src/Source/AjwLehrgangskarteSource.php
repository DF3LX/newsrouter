<?php

namespace DARCNews\Source;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\Logger;
use DARCNews\Core\News;

/**
 * Summary of AjwLehrgangskarteSource
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class AjwLehrgangskarteSource extends SourceBase
{
    /**
     * Implementierung der abstrakten Basisklassenmethode, die eine genaue Beschreibung des Filters liefert.
     * @return string   Beschreibung
     */
    public static /*abstactImpl*/function getDescription(): string
    {
        return "Ich kann Lehrgänge auslesen";
    }

    /**
     * Summary of FetchNews
     * @throws \Exception
     * @return int
     */
    public function FetchNews(): int
    {
        throw new \Exception("Not implemented");
    }


    /**
     * Prüft, ob der Filter aktiviert werden kann.
     * @return bool Flag, ob der Filter aktiviert werden kann.
     */
    protected /*abstractImpl*/function canEnable(): bool
    {
        return true;
    }

    /**
     * Summary of doStuff
     * @return int
     */
    protected /*abstractImpl*/function doStuff(): int
    {
        try
        {
            $options = array(
                'http' => array(
                    'protocol_version' => '1.1',
                    'method' => 'GET',
                    'header' => array(
                        'Accept: application/json',
                        'User-Agent: DARC NewsRouter AjwLehrgangsKarteSource v1.0',
                    ),
                )
            );

            $resultText = file_get_contents('http://ajw.darc.de/lehrgangskarte/api/v1/kurse', false, stream_context_create($options)); // send https request

            if ($resultText === false)
            {
                throw new \ErrorException("Fehler beim Aufruf der Lehrgnags-API: {$http_response_header[0]}");
            }

            $results = json_decode($resultText, true); // decode JSON
            
            $itemCount = 0;

            foreach ($results as $item)
            {
                /*[{"id":"1",
                    "Call":"DL1KMH",
                    "Vorname":"Harald",
                    "Nachname":"Metzen",
                    "DOK":"G14",
                    "OV":"Herzogenrath",
                    "EMail":"dl1kmh@darc.de",
                    "Tel":"+49 2407 918144",
                    "Strasse":"Comeniusstra\u00dfe 8",
                    "PLZ":"52134",
                    "Ort":"Herzogenrath",
                    "KursArt":"pr\u00e4senz",
                    "KursStart":"date",
                    "KursStartDate":"2016-09-08",
                    "KursStartTime":"",
                    "KursEnd":"date",
                    "KursEndDate":"2017-02-02",
                    "KursTypE":"0",
                    "KursTypA":"1",
                    "KursTypCW":"0",
                    "KursTypSWL":"0",
                    "KursGebuehr":"bitte fragen",
                    "KursURL":"http:\/\/www.darc.de\/g14",
                    "KursText":"Der Kurs in Zusammenarbeit mit der VHS Nordkreis Aachen beginnt mit der Infoveranstaltung am 8. September 2016 und findet w\u00f6chentlich donnerstags von 18:30 bis 20:00 Uhr im B\u00fcrgerhaus Merkstein, Comeniusstr. 8, Herzogenrath statt. Der Lehrgang endet voraussichtlich im Februar 2017 mit der Pr\u00fcfung bei der Bundesnetzagentur.",
                    "MapLat":"50.8960675","MapLon":"6.124185","KursStatus":"4"},
                */

                Logger::Info(static::class . " ({$this->getName()}): Verarbeite Lehrgang {$item['id']}");

                $klassen = array();
                $klasseText = '';
                if ($item['KursTypN'] == "1")
                {
                    $klasseText = 'Klasse ';
                    $klassen[] = 'N';
                }
                if ($item['KursTypE'] == "1")
                {
                    $klasseText = 'Klasse ';
                    $klassen[] = 'E';
                }
                if ($item['KursTypA'] == "1")
                {
                    $klasseText = 'Klasse ';
                    $klassen[] = 'A';
                }
                if ($item['KursTypCW'] == "1")
                {
                    $klassen[] = 'Morsen';
                }
                if ($item['KursTypSWL'] == "1")
                {
                    $klassen[] = 'SWL Prüfung';
                }

                $klasseText .= (count($klassen) == 2) ? implode(' & ', $klassen) : implode(', ', $klassen);

                if ($item['KursStart'] == 'date')
                {
                    $start = \DateTime::createFromFormat('Y-m-d', $item['KursStartDate'])->format('d.m.Y');

                    $Nachricht = "Der Ortsverband {$item['OV']} ({$item['DOK']}) veranstaltet ab dem {$start} einen Amateurfunklehrgang für "
                        . "{$klasseText}"
                        . (in_array($item['KursArt'], ['präsenz', 'gemeinsam']) ? " in {$item['PLZ']} {$item['Ort']}." : ".")
                        . "\n"
                        . "Weitere Infos gibt es bei {$item['Vorname']} {$item['Nachname']} {$item['Call']} ({$item['EMail']})"
                        . ((strlen($item['KursURL']) > 1) ? " oder unter {$item['KursURL']}." : '.');
                }
                else
                {
                    $Nachricht = "Der Ortsverband {$item['OV']} ({$item['DOK']}) kann jederzeit einen Amateurfunklehrgang für "
                        . "{$klasseText}"
                        . (in_array($item['KursArt'], ['präsenz', 'gemeinsam']) ? " in {$item['PLZ']} {$item['Ort']}" : "")
                        . " beginnen.\n"
                        . "Weitere Infos gibt es bei {$item['Vorname']} {$item['Nachname']} {$item['Call']} ({$item['EMail']})"
                        . ((strlen($item['KursURL']) > 1) ? " oder unter {$item['KursURL']}." : '.');
                }

                Logger::Debug($Nachricht);

                $result = $this->saveMessage(
                    News::CreateNew(
                        $item['id'],
                        new \DateTime(),
                        "Neuer Lehrgang für {$klasseText}",
                        null,
                        $Nachricht,
                        $item['KursURL'],
                        null,
                        $item // wer weiss, ob man die noch mal braucht
                    )
                );

                if ($result == ErrorCodes::AlreadyExists)
                {
                    Logger::Error(static::class . " ({$this->getName()}): Die Kursmeldung {$item['id']} ist bereits vorhanden\n");
                    continue;
                }

                $itemCount++;
            } // foreach
            return $itemCount;
        }
        catch (\Exception $ex)
        {

            Logger::Error(static::class . " ({$this->getName()}): Fehler beim abholen und formatieren von Lehrgängen\n");
            Logger::Error($ex->getMessage());

            return 0;
        }
    }
}