<?php

namespace DARCNews\Filter;
use DARCNews\Core\MessageState;
use DARCNews\Core\ModuleBase;
use DARCNews\Core\ByState;
use DARCNews\Core\News;


/**
 * Basisklasse für alle Filter, kann Nachrichten raussuchen und auf verarbeitet setzen
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class FilterBase extends ModuleBase
{
    /**
     * Sucht eine noch nicht von diesem Filter verarbeitete Nachricht raus
     * @return ?\DARCNews\Core\News  Nachricht oder NULL wenn keine Nachricht da
     */
    protected function getUnprocessedMessage(): ?News
    {
        global $pdo;
        // Gesamte Nachricht + Body raussuchen, für die noch kein "filteredBy" Eintrag existiert
        $statement = $pdo->prepare("SELECT IM.*, IMD.* FROM InputMessage IM INNER JOIN InputMessageData IMD on IM.ID = IMD.ID"
            . " LEFT JOIN FilteredBy Fil on IM.ID = Fil.InputMessageId and Fil.FilterID = :filterid WHERE IM.State = " . MessageState::Neu . " AND Fil.InputMessageId IS NULL");
        $statement->bindValue(":filterid", $this->getId());
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $statement = null;

        if ($result != null)
            return News::CreateFromDB($result);
        else
            return null;
    }

   
    

      /**
     * Setze die Nachricht auf verarbeitet, indem ein entsprechender FilteredBy-Eintrag angelegt wird
     * @param \DARCNews\Core\News $Message  Nachricht
     * @param bool $Match                   Flag, ob der Filter erfolgreich angeschlagen hat
     * @return void                         nix, gar nix
     */
    protected function setMessageProcessed(News $Message, bool $Match): void
    {
        global $pdo;
        $statement = $pdo->prepare("INSERT INTO FilteredBy(InputMessageId, FilterId, State) VALUES(:inputmessageid, :filterid, :state)");
        $statement->bindValue(":inputmessageid", $Message->getId());
        $statement->bindValue(":filterid", $this->getId());
        $statement->bindValue(":state", (
            $this->isCatchUp() // Wenn CatchUp, dann werden die Nachrichten als CatchUp markiert
            ? ByState::CatchUp
            : ($Match
                ? ByState::Successful
                : ByState::NoMatch)
        ));
        $statement->execute();
        $statement->closeCursor();
        $statement = null;
    }
}