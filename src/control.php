<?php

/**
 * Durch Starten dieses Scripts kann der DARC-News-Router konfiguriert werden
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
*/

namespace DARCNews;
use DARCNews\Channel\ChannelController;
use DARCNews\Source\SourceController;
use DARCNews\Filter\FilterController;
use DARCNews\Formatter\FormatterController;
use DARCNews\Core\ErrorCodes;

$dbConnectionString = 'pgsql:host=localhost;dbname=darcnews;user=darcnews;password=darcnews';
$pdo = new \PDO($dbConnectionString);

spl_autoload_register(function ($class_name)
{
    include str_replace("\\", DIRECTORY_SEPARATOR, str_replace(__NAMESPACE__ . '\\', '', $class_name . '.php'));
});

/**
 * Summary of DARCNews\myStrTok
 * @param mixed $Line
 * @return mixed
 */
function myStrTok(&$Line) : ?string
{
    if ($Line == null)
        return null;

    $idx = strpos($Line, " ");

    if ($idx === false)
    {
        $result = $Line;
        $Line = null;
    }
    else
    {
        $result = substr($Line, 0, $idx);
        $Line=substr($Line, $idx+1);
    }
    return $result;
}


function printHelp($Level)
{
   
    \CliColor::echo(<<<END
        |WH||U1|verfügbare Kommandos sind|U0|
        |WH|generelle Befehle
        |WH||U1|h|U0|elp|x| dieser Hilfetext
        |WH||U1|q|U0|uit|x| beendet das Programm
        |WH||U1|..|U0||x|   springt eine Ebene höher
        |WH||U1|\\|U0||x|    springt zur höchsten Ebene
        |WH|\\<Befehl>|x|   führt den Befehl auf der höchsten Ebene aus
        \n
        END);
    switch ($Level)
    {
        case 0:
            \CliColor::echo(<<<END
                |WH|Mögliche Controller sind
                |YE||U1|So|U0|urce|x|     Steuert Input-Kanäle
                |YE||U1|Fi|U0|lter|x|     Steuert Nachrichtenfilter
                |YE||U1|Fo|U0|rmatter|x|  Steuert Nachrichten-Formatierer
                |YE||U1|Ch|U0|annel|x|    Steuert Ausgabe-Kanäle
                \n
                END);
        case 1:
            \CliColor::echo(<<<END
                |WH|Folgende Befehle sind für alle Controller verfügbar
                |YE|<Controller> |WH||U1|L|U0|ist|x| - zeige die registrierten Module
                |YE|<Controller> |WH||U1|S|U0|how|x| - zeige die verfügbaren Modultypen
                |YE|<Controller> |WH||U1|A|U0|dd |BL|<Modulname> |DRD|<Typ>|x| - füge ein neues Modul mit dem angebenen Namen und Typ hinzu
                \n
                END);
        case 2:
            \CliColor::echo(<<<END
                |WH|Folgende Befehle sind für alle Module verfügbar
                |YE|<Controller> |BL|<Modulname> |WH||U1|E|U0|nable|x|  - aktiviere das Modul
                |YE|<Controller> |BL|<Modulname> |WH||U1|D|U0|isable|x| - deaktiviere das Modul
                |YE|<Controller> |BL|<Modulname> |WH||U1|C|U0|atchUp|x| - setze alle Nachrichten auf verarbeitet
                |YE|<Controller> |BL|<Modulname> |WH||U1|I|U0|nfo|x|    - Zeige Hilfetext zum Modul an
                |YE|<Controller> |BL|<Modulname> |WH||U1|Sh|U0|ow|x|    - Zeige die verfügbaren Parameter des Moduls an
                |YE|<Controller> |BL|<Modulname> |WH||U1|G|U0|et |CY|<Parameter>|x| - fragt einen modulspezifischen Parameter ab
                |YE|<Controller> |BL|<Modulname> |WH||U1|S|U0|et |CY|<Parameter> |MA|<Wert>|x| - setze einen modulspezifischen Parameter
                
                |WH|Module vom Typ |YE|Formatter|x| kennen folgende Parameter:
                 |CY||U1|Ch|U0|annel|x| |MA|<ChannelName>|x| - Der Ausgabekanal, für dieser Formatter Nachrichten erstellt
                 |CY||U1|Fi|U0|lter|x|  |MA|<FilterName>|x|  - Der Filter, der diesen Formatter aktiviert
                 \n
                END);
            break;
    }
}

$cmdPrefix = [];

function Prompt($cmds)
{
    $colorArr = [\CliColor::YELLOW, \CliColor::CYAN, \CliColor::WHITE, \CliColor::RESET, \CliColor::RESET, \CliColor::RESET];

    echo \CliColor::BGLIGHTBLACK . \CliColor::WHITE . "DARCNews\\";

    for ($i = 0; $i < count($cmds); $i++)
        echo $colorArr[$i] . $cmds[$i] . "\\";
    echo \CliColor::WHITE . ">" . \CliColor::RESET ." ";
}

while (true)
{
    do
    {
        Prompt($cmdPrefix);
     //   $inputLine = readline("");
     //   readline_add_history($inputLine);
        $inputLine = trim(fgets(STDIN));

        // wenn \ lösche den Prefix-Buffer
        if (strlen($inputLine) == 1 && $inputLine[0] == "\\")
        {
            $cmdPrefix = []; // empty array
        }
        elseif (strlen($inputLine) > 1 && $inputLine[0] == "\\")
        {
            $line = substr($inputLine, 1);
        }
        else // ansonsten füge Prefixbuffer vor dem Kommando ein
        {
            array_push($cmdPrefix, $inputLine);
            $line = implode(" ", $cmdPrefix);
            array_pop($cmdPrefix);
        }
    } while (empty($inputLine));


    // Hauptkommandos:
    $cmd = myStrTok($line);
    switch (substr(strtolower($cmd ?? ""), 0, 2))
    {
        case "":
            \CliColor::Warn("Keine Eingabe\n");
            break;

        case "..":
            array_pop($cmdPrefix);
            break;

        case "q":
        case "qu":
            exit();
            break;

        case "te":
            \CliColor::Test();
            break;

        case "?":
        case "h":
        case "he":
            printHelp(0);
            break;

        default: // alles andere ist ein Controller-Name
            $controller = null;

            switch (substr(strtolower($cmd ?? ""), 0, 2))
            {
                case "s":
                case "so":
                    $cmd = "Source";
                    $controller = SourceController::getInstance();
                    break;

                case "fi":
                    $cmd = "Filter";
                    $controller = FilterController::getInstance();
                    break;

                case "fo":
                    $cmd = "Formatter";
                    $controller = FormatterController::getInstance();
                    break;

                case "c":
                case "ch":
                    $cmd = "Channel";
                    $controller = ChannelController::getInstance();
                    break;
            }

            if ($controller == null)
            {
                \CliColor::Error("unbekannter Befehl oder Controller nicht bekannt\n");
                break;
            }

            // Auswertung der SubCmds für den Controller

            $subCmd = myStrTok($line);
            switch (substr(strtolower($subCmd ?? ""), 0, 2))
            {
                case "": // übernehme aktulles Kommando in den Befehlsspeicher
                    if (strlen($inputLine) > 1 && $inputLine[0] == "\\")
                    {
                        $cmdPrefix = [];
                    }
                    array_push($cmdPrefix, $cmd);
                    break;

                case "..":
                    array_pop($cmdPrefix);
                    break;

                case "?":
                case "h":
                case "he":
                    printhelp(empty($line) ? 1 : 0);
                    break;

                case "l":
                case "li":
                case "p":
                case "pr":
                    $modulesList = $controller->getModulesRegistered();

                    \CliColor::Highlight("Folgende Module sind registriert: \n");
                    foreach ($modulesList as $row)
                    {
                        \CliColor::echo(<<<END
                            {$row['Id']} \t Name: |BL|{$row['Name']}|x| 
                            \t Typ:  |DRD|{$row['TypeName']}|x| 
                            \t Info: |W1|{$row['Description']}|W0|

                            END);
                    }
                    echo "\n";
                    break;

                case "s":
                case "sh":
                    $modulesList = $controller->getModulesAvailable();

                    \CliColor::Highlight("Verfügbare Modultypen sind:\n\n");
                    foreach ($modulesList as $row)
                    {
                        \CliColor::echo(<<<END
                            Typ:  |DRD|{$row['TypeName']}|x|
                            Info: |W1|{$row['Description']}|W0|
                            \n
                            END);
                    }
                    echo "\n";
                    break;

                case "a":
                case "ad":
                    $name = myStrTok($line);

                    if (in_array($name, ["help", "print", "typ", "show", "add", "enable", "disable"]))
                    {
                        \CliColor::Error("Ein Modul kann nicht wie ein Befehl heißen.\n\n");
                        break;
                    }

                    $result = $controller->addModule($name, $line);
                    switch ($result)
                    {
                        case ErrorCodes::NotEnoughArguments:
                            \CliColor::Error("Zu wenig Argumente angegeben. Syntax: |WH|Add |BL|<Modulname> |DRD|<Typ>|x|\n\n");
                            break;

                        case ErrorCodes::ModulNotFound:
                            \CliColor::Error("Ein Modultyp mit dem Namen \"|BL|{$line}|u|\" existiert nicht.\n\n");
                            break;

                        case ErrorCodes::AlreadyExists:
                            \CliColor::Error("Ein Modul mit dem Namen |BL|{$name}|u| existiert schon\n\n");
                            break;
                        case ErrorCodes::OK:
                            \CliColor::Success("Modul |BL|{$name}|u| vom Typ |MA|{$line}|u| erfolgreich registriert\n\n");
                            break;
                        default:
                            \CliColor::Error("Unbekanntes Ergebnis\n");
                            break;
                    }
                    break;

                default: // alles andere ist ein Modul Name

                    $modules = $controller->getModulesRegistered();
                    $modulNames = array_column($modules, "Name");

                    $modulName = false;
                    foreach ($modulNames as $try)
                    {
                        if (strncasecmp($try, $subCmd, strlen($subCmd)) == 0)
                        {
                            $modulName = $try;
                            break;
                        }
                    }
                    // if ($modulName == null)
                    // {
                    //     \CliColor::Error("Unbekannter Befehl oder Modul nicht bekannt.\n\n");
                    //     break;
                    // }

                    // Auswertung der Subcommands für das Modul
                    $modulCmd = myStrTok($line);

                    switch (substr(strtolower($modulCmd ?? ""), 0, 2))
                    {
                        case "":
                            if ($modulName == null)
                            {
                                \CliColor::Error("Unbekannter Befehl oder Modul nicht bekannt.\n\n");
                                break 2;
                            }
                            array_push($cmdPrefix, $modulName);
                            break 2;

                        case "..":
                            array_pop($cmdPrefix);
                            break 2;

                        case "?":
                        case "h":
                        case "he":
                            printhelp(empty($line) ? 2 : 0);
                            break 2;

                        case "e":
                        case "en":
                            $result = $controller->setEnabled($modulName, true);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === ErrorCodes::OK:
                                    \CliColor::Success("Das Modul |BL|{$modulName}|u| wurde aktiviert.\n\n");
                                    break;

                                default:
                                    \CliColor::Warn("Unbekannter Fehler: {$result}\n");
                                    break;
                            }
                            break 2;

                        case "d":
                        case "di":
                            $result = $controller->setEnabled($modulName, false);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === ErrorCodes::OK:
                                    \CliColor::Success("Das Modul |BL|{$modulName}|u| wurde deaktiviert.\n\n");
                                    break;
                                default:
                                    \CliColor::Warn("Unbekannter Fehler: {$result}\n");
                                    break;
                            }
                            break 2;

                        case "c":
                        case "ca":
                            $result = $controller->catchUp($modulName);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === ErrorCodes::OK:
                                    \CliColor::Success("Das Modul |BL|{$modulName}|u| wurde deaktiviert.\n\n");
                                    break;
                                case $result === ErrorCodes::Operation_Failed:
                                    \CliColor::Error("Das Modul |BL|{$modulName}|u| ist nicht deaktiviert.\n\n");
                                    break;
                                default:
                                    \CliColor::Warn("Unbekannter Fehler: {$result}\n");
                                    break;
                            }
                            break 2;

                        case "i":
                        case "in":
                            $result = $controller->getHelp($modulName);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === "":
                                    \CliColor::Warn("Keine Info für das Modul |BL|{$modulName}|u| vorhanden.\n\n");
                                    break;
                                default:
                                    \CliColor::Print($result . "\n");
                                    break;
                            }
                            break 2;

                        case "sh":
                            $result = $controller->getParameterInfo($modulName);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                default:
                                    \CliColor::Print("Übersicht der Parameter:\n");
                                    foreach ($result as $key => $data)
                                    {
                                        if (is_array($data['Value']))
                                            \CliColor::echo("|CY|{$key}|x|: |YE|Multivalue, bitte mit get abfragen|x|\n|WH|{$data['Description']}|x|\n\n");
                                        else
                                            
                                            \CliColor::echo("|CY|{$key}|WH|: |MA|{$data['Value']}|WH|\n{$data['Description']}|x|\n\n");
                                    }
                                    break;
                            }
                            break 2;

                        case "g":
                        case "ge":
                            $parameter = myStrTok($line);
                            if ($parameter == null)
                            {
                                \CliColor::Error("Kein Parameter angegeben\n");
                                break 2;
                            }

                            $result = $controller->getParameter($modulName, $parameter);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === "":
                                    \CliColor::Warn("Der Parmeter |CY|{$parameter}|u| existiert nicht oder ist leer.\n\n");
                                    break;

                                case is_array($result):
                                    \CliColor::Highlight("Der Parameter |CY|{$parameter}|u| enthält folgende Werte:\n");
                                    foreach ($result as $entry)
                                        \CliColor::Print("-> |MA|{$entry}|u|\n");
                                    break;
                                default:
                                    \CliColor::Highlight("Der Wert des Parameters |CY|{$parameter}|u| ist: |MA|{$result}|x|\n\n");
                                    break;
                            }
                            break 2;

                        case "s":
                        case "se":
                            $parameter = myStrTok($line);

                            if ($parameter == null)
                            {
                                \CliColor::Error("Kein Parameter angegeben.\n");
                                break 2;
                            }

                            $result = $controller->setParameter($modulName, $parameter, $line);
                            switch (true)
                            {
                                case $result === null:
                                    \CliColor::Error("Kein Modul mit dem Namen |BL|{$subCmd}|u| gefunden.\n\n");
                                    break;

                                case $result === ErrorCodes::ParameterNotFound:
                                    \CliColor::Error("Der Parameter |CY|{$parameter}|u| ist nicht bekannt.\n\n");
                                    break;

                                case $result === ErrorCodes::ValueNotFound:
                                    \CliColor::Error("Der Wert |MA|{$line}|u| ist für den Parameter |CY|{$parameter}|u| nicht gültig.\n\n");
                                    break;

                                case $result === ErrorCodes::OK:
                                    \CliColor::Success("Der Parameter |CY|{$parameter}|u| wurde auf |MA|{$line}|u| gesetzt.\n\n");
                                    break;

                                default:
                                    \CliColor::Warn("Unbekannter Fehler: {$result}\n");
                                    break;
                            }
                            break 2;
                    }

                    \CliColor::Error("Unbekannter Modulbefehl.\n\n");

                    break;
            }
            break;
    }
}