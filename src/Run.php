<?php

/**
 * Dies ist der Runner, der den DARC-News-Router ausführt
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
*/

namespace DARCNews;
use \CliColor;
use DARCNews\Channel\ChannelController;
use DARCNews\Source\SourceController;
use DARCNews\Filter\FilterController;
use DARCNews\Formatter\FormatterController;

$dbConnectionString = 'pgsql:host=localhost;dbname=darcnews;user=darcnews;password=darcnews';
$pdo = new \PDO($dbConnectionString);

spl_autoload_register(function ($class_name)
{
    include str_replace("\\", DIRECTORY_SEPARATOR, str_replace(__NAMESPACE__ . '\\', '', $class_name . '.php'));
});

//include "CliColor.php";

CliColor::Print("Starte SourceController\n");
SourceController::getInstance()->run(false);

\CliColor::Print("Starte FilterController\n");
FilterController::getInstance()->run(false);

\CliColor::Print("Starte FormatterController\n");
FormatterController::getInstance()->run(false);

\CliColor::Print("Starte ChannelController\n");
ChannelController::getInstance()->run(false);

\CliColor::Success("Fertig\n");