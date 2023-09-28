<?php

namespace DARCNews\Source;
use DARCNews\Core\ErrorCodes;
use DARCNews\Core\MessageState;
use DARCNews\Core\ModuleBase;
use DARCNews\Core\News;

/**
 * Summary of SourceBase
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class SourceBase extends ModuleBase
{

    /**
     * Summary of doStuff
     * @return int
     */
    protected abstract function doStuff() : int;

   
    /**
     * Prüft, ob die Nachricht bereits bekannt ist
     * @param string $UniqueId  Nachrichten-ID im Quellsystem
     * @return bool             TRUE wenn Nachricht bereits bekannt ist.
     */
    protected function isMessageKnown(string $UniqueId): bool
    {
        global $pdo;
        $statement = $pdo->prepare("SELECT count(UniqueId) from InputMessage where SourceId = :sourceid and UniqueId = :uniqueid");
        $statement->bindValue(":sourceid", $this->getId());
        $statement->bindValue(":uniqueid", $UniqueId);
        $statement->execute();
        $foundRows = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;

        return ($foundRows > 0);
    }

   
    /**
     * Speichert eine nachricht aus einem Quellsystem ab
     * @param \DARCNews\Core\News $Message  Nachrichtenstruktur
     * @return int                          ErrorCode, ob die FUnktion erfolgreich war oder die Nachricht bereits existiert
     */
    protected function saveMessage(News $Message): int
    {
        if ($this->isMessageKnown($Message->getUniqueId()))
            return ErrorCodes::AlreadyExists;

        global $pdo;
        $pdo->beginTransaction();
        $statement = $pdo->prepare("INSERT INTO InputMessage (CreatedAt, SourceId, UniqueId, State) VALUES(:createdAt, :sourceId, :uniqueId, :state) RETURNING Id");
        $statement->bindValue(":createdAt", $Message->getCreatedAt()->format('Y-m-d H:i:s'));
        $statement->bindValue(":sourceId", $this->getId());
        $statement->bindValue(":uniqueId", $Message->getUniqueId());
        $statement->bindValue(":state", $this->isCatchUp() ? MessageState::CatchUp : MessageState::Neu);    /// Wenn CatchUp, dann Nachrichten als CatchUp Abspeichern, damit wir die IDs wissen
        $statement->execute();
        $dbId = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;

        // uns interessieren uns die Texte von CatchUp-Nachrichten nicht
        if (!$this->isCatchUp())
        {
            $statement = $pdo->prepare("INSERT INTO InputMessageData(Id, Titel, Teaser, Text, Permalink, Image, Metadata) VALUES(:id, :titel, :teaser, :text, :permalink, :image,:metadata)");
            $statement->bindValue(":id", $dbId);
            $statement->bindValue(":titel", $Message->getTitel());
            $statement->bindValue(":teaser", $Message->getTeaser());
            $statement->bindValue(":text", $Message->getText());
            $statement->bindValue(":permalink", $Message->getPermalink());
            $statement->bindValue(":image", $Message->getImage(), \PDO::PARAM_LOB);
            $statement->bindValue(":metadata", $Message->getMetadataString());
            $statement->execute();
            $statement->closeCursor();
            $statement = null;
        }
        $pdo->commit();

        return ErrorCodes::OK;
    }
}