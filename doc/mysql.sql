SET @@session.sql_mode = '';

CREATE TABLE `formatter` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `enabled` BOOLEAN DEFAULT 0 NOT NULL,
    `filterid` INT,
    `channelid` INT,
    `classname` VARCHAR(64) NOT NULL,
    `parameters` TEXT,
    PRIMARY KEY (`id`)
);

CREATE TABLE `_bystate` (
    `id` SMALLINT NOT NULL,
    `name` VARCHAR(64),
    PRIMARY KEY (`id`)
);

CREATE TABLE `_messagestate` (
    `id` SMALLINT NOT NULL,
    `name` VARCHAR(64),
    PRIMARY KEY (`id`)
);

CREATE TABLE `channel` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `enabled` BOOLEAN DEFAULT 0 NOT NULL,
    `classname` VARCHAR(64) NOT NULL,
    `parameters` TEXT,
    PRIMARY KEY (`id`)
);

CREATE TABLE `filter` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `enabled` BOOLEAN DEFAULT 0 NOT NULL,
    `classname` VARCHAR(64) NOT NULL,
    `parameters` TEXT,
    PRIMARY KEY (`id`)
);

CREATE TABLE `filteredby` (
    `inputmessageid` BIGINT NOT NULL,
    `filterid` INT NOT NULL,
    `state` SMALLINT NOT NULL,
    PRIMARY KEY (`inputmessageid`, `filterid`)
);

CREATE TABLE `formattedby` (
    `inputmessageid` BIGINT NOT NULL,
    `filterid` INT NOT NULL,
    `formatterid` INT NOT NULL,
    `state` SMALLINT DEFAULT 0 NOT NULL
);

CREATE TABLE `source` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `enabled` BOOLEAN DEFAULT 0 NOT NULL,
    `classname` VARCHAR(64) NOT NULL,
    `parameters` TEXT,
    PRIMARY KEY (`id`)
);

CREATE TABLE `inputmessage` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `createdat` TIMESTAMP NOT NULL,
    `sourceid` BIGINT NOT NULL,
    `uniqueid` VARCHAR(128) NOT NULL,
    `state` SMALLINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `inputmessagedata` (
    `id` BIGINT NOT NULL,
    `titel` VARCHAR(1000),
    `teaser` VARCHAR(2000),
    `text` TEXT NOT NULL,
    `permalink` VARCHAR(256),
    `image` LONGBLOB,
    `metadata` TEXT
);

CREATE TABLE `outputmessage` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `createdat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `formatterid` INT NOT NULL,
    `channelid` INT NOT NULL,
    `inputmessageid` BIGINT NOT NULL,
    `state` SMALLINT DEFAULT 0 NOT NULL,
    `sequence` INT NOT NULL,
    `uniqueid` VARCHAR(128)
);

CREATE TABLE `outputmessagedata` (
    `id` BIGINT NOT NULL,
    `titel` VARCHAR(1000),
    `teaser` VARCHAR(2000),
    `text` TEXT NOT NULL,
    `permalink` VARCHAR(256),
    `image` LONGBLOB,
    `metadata` TEXT
);

ALTER TABLE `channel` AUTO_INCREMENT = 1;
ALTER TABLE `filter` AUTO_INCREMENT = 1;
ALTER TABLE `formatter` AUTO_INCREMENT = 1;
ALTER TABLE `inputmessage` AUTO_INCREMENT = 1;
ALTER TABLE `outputmessage` AUTO_INCREMENT = 1;
ALTER TABLE `source` AUTO_INCREMENT = 1;

INSERT INTO `_bystate` VALUES (0, 'No Match');
INSERT INTO `_bystate` VALUES (-1, 'Error');
INSERT INTO `_bystate` VALUES (1, 'Match/Success');
INSERT INTO `_bystate` VALUES (2, 'CatchUp');
INSERT INTO `_messagestate` VALUES (0, 'Neue Nachricht');
INSERT INTO `_messagestate` VALUES (1, 'Nachricht verarbeitet');
INSERT INTO `_messagestate` VALUES (2, 'Nachrichtenbody gelöscht');
INSERT INTO `_messagestate` VALUES (3, 'durch CatchUp abgespeichert');
INSERT INTO `_messagestate` VALUES (-1, 'Fehler bei der Verarbeitung');

-- Add foreign key constraints here, if needed
ALTER TABLE `filteredby`
    ADD CONSTRAINT `FilteredBy_FilterId_fkey` FOREIGN KEY (`filterid`) REFERENCES `filter`(`id`) ON DELETE CASCADE;

ALTER TABLE `filteredby`
    ADD CONSTRAINT `FilteredBy_InputMessageId_fkey` FOREIGN KEY (`inputmessageid`) REFERENCES `inputmessage`(`id`) ON DELETE CASCADE;

ALTER TABLE `filteredby`
    ADD CONSTRAINT `FilteredBy_State_fkey` FOREIGN KEY (`state`) REFERENCES `_bystate`(`id`);

ALTER TABLE `formatter`
    ADD CONSTRAINT `Formatter_ChannelId_fkey` FOREIGN KEY (`channelid`) REFERENCES `channel`(`id`) ON DELETE CASCADE;

ALTER TABLE `formatter`
    ADD CONSTRAINT `Formatter_FilterId_fkey` FOREIGN KEY (`filterid`) REFERENCES `filter`(`id`) ON DELETE CASCADE;

ALTER TABLE `inputmessage`
    ADD CONSTRAINT `InputMessage_SourceId_fkey` FOREIGN KEY (`sourceid`) REFERENCES `source`(`id`) ON DELETE CASCADE;

ALTER TABLE `inputmessage`
    ADD CONSTRAINT `InputMessage_State_fkey` FOREIGN KEY (`state`) REFERENCES `_messagestate`(`id`);

ALTER TABLE `outputmessagedata`
    ADD CONSTRAINT `OutputMessageData_Id_fkey` FOREIGN KEY (`id`) REFERENCES `outputmessage`(`id`) ON DELETE CASCADE;

ALTER TABLE `outputmessage`
    ADD CONSTRAINT `OutputMessage_ChannelId_fkey` FOREIGN KEY (`channelid`) REFERENCES `channel`(`id`) ON DELETE CASCADE;

ALTER TABLE `outputmessage`
    ADD CONSTRAINT `OutputMessage_FormatterId_fkey` FOREIGN KEY (`formatterid`) REFERENCES `formatter`(`id`) ON DELETE CASCADE;

ALTER TABLE `outputmessage`
    ADD CONSTRAINT `OutputMessage_InputMessageId_fkey` FOREIGN KEY (`inputmessageid`) REFERENCES `inputmessage`(`id`) ON DELETE CASCADE;

ALTER TABLE `outputmessage`
    ADD CONSTRAINT `OutputMessage_State_fkey` FOREIGN KEY (`state`) REFERENCES `_messagestate`(`id`);

ALTER TABLE `formattedby`
    ADD CONSTRAINT `ProcessedBy_FilterId_fkey` FOREIGN KEY (`filterid`) REFERENCES `filter`(`id`) ON DELETE CASCADE;

ALTER TABLE `formattedby`
    ADD CONSTRAINT `ProcessedBy_FormatterId_fkey` FOREIGN KEY (`formatterid`) REFERENCES `formatter`(`id`) ON DELETE CASCADE;

ALTER TABLE `formattedby`
    ADD CONSTRAINT `ProcessedBy_InputMessageId_fkey` FOREIGN KEY (`inputmessageid`) REFERENCES `inputmessage`(`id`) ON DELETE CASCADE;

ALTER TABLE `formattedby`
    ADD CONSTRAINT `ProcessedBy_State_fkey` FOREIGN KEY (`state`) REFERENCES `_bystate`(`id`);

-- Weitere SQL-Anweisungen und Anpassungen