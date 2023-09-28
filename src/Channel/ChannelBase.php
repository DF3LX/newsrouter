<?php

namespace DARCNews\Channel;
use DARCNews\Core\MessageState;
use DARCNews\Core\ModuleBase;
use DARCNews\Core\News;


/**
 * Basisklasse für alle Ausgabekanäle, kann Nachrichten raussuchen, absenden und auf verarbeitet setzen
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class ChannelBase extends ModuleBase
{
    /**
     * Sucht eine noch nicht auf dem Ausgabekanal abgesendete Nachricht raus
     * @return array|bool   Nachricht oder bool false wenn keine Nachricht da
     */
    protected function getUnprocessedMessage(): ?News
    {
        global $pdo;
        // Join zwischen OutputMessage und OutputMessageData um vollständigen Datensatz zu ehalten
        $statement = $pdo->prepare("SELECT OM.*, OMD.Titel, OMD.Teaser, OMD.Text, OMD.PermaLink, OMD.Image, OMD.Metadata, IM.sourceid FROM OutputMessage OM INNER JOIN OutputMessageData OMD on OM.ID = OMD.ID INNER JOIN InputMessage IM on OM.InputMessageId = IM.ID WHERE ChannelId = :channelid and  OM.State = " . MessageState::Neu);
        $statement->bindValue(":channelid", $this->getId());
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
     * Setze die Nachricht auf verarbeitet, wenn sie auf erfolgreich auf dem Ausgabekanal abgesendet wurde.
     * @param int $MessageId    ID der Nachricht
     * @param bool $Erfolg      Flag, ob der Filter erfolgreich angeschlagen hat
     * @param mixed $UniqueId   Eindeutige Id, die die Nachricht vom Zielsystem bekommen hat
     * @return void             nix, gar nix
     */
    protected function setMessageProcessed(News $Message, bool $Erfolg, ?string $UniqueId): void
    {
        global $pdo;
        // nur Nachrichtenkopf updaten
        $statement = $pdo->prepare("UPDATE OutputMessage set State = :state, UniqueId = :uniqueid where id = :id");
        $statement->bindValue(":id", $Message->getId());
        $statement->bindValue(":uniqueid", $UniqueId ?? $Message->getUniqueId());   // Wenn schon kein Parameter, dann ists vllt in der Nachricht drin
        $statement->bindValue(":state",
            ($this->isCatchUp() // Wenn CatchUp, dann werden die Nachrichten als CatchUp markiert
                ? MessageState::CatchUp
                : ($Erfolg
                    ? MessageState::Processed
                    : MessageState::Error)
            ));
        $statement->execute();
        $statement->closeCursor();
        $statement = null;

    }
}