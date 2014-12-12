--  Changeset updates.2013.09::users_al::Foxel::(Checksum: 3:870bc90c417aa32fb29028c7983672d6)
CREATE TABLE `qfso_users_al` (`id` CHAR(32)
                                   CHARACTER SET ascii NOT NULL, `user_id` INT UNSIGNED NULL, `user_sig` CHAR(32)
                                   CHARACTER SET ascii NOT NULL, `starttime` INT NOT NULL, `lastused` INT NULL, CONSTRAINT `PK_QFSO_USERS_AL` PRIMARY KEY (`id`))
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'updates.2013.09', 'users_al', '2.0.5', '3:870bc90c417aa32fb29028c7983672d6', 28);

--  Changeset updates.2013.09::init-sessions-indexes::Foxel::(Checksum: 3:d17b840d8f6bdcc80f67633b4c990a48)
CREATE INDEX `user_id` ON `qfso_users_al` (`user_id`);

CREATE INDEX `lastused` ON `qfso_users_al` (`lastused`);

CREATE INDEX `starttime` ON `qfso_users_al` (`starttime`);

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x3)', 'EXECUTED', 'updates.2013.09', 'init-sessions-indexes', '2.0.5', '3:d17b840d8f6bdcc80f67633b4c990a48', 29);
