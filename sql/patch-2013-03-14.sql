
--  Changeset updates.2013.03::files-create::Foxel::(Checksum: 3:38016f5f8890b4e15470c93b031d7533)
CREATE TABLE `qfso_files` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `path_sha1` VARCHAR(40) NOT NULL, `user_id` INT UNSIGNED NOT NULL, `acc_lvl` TINYINT UNSIGNED DEFAULT 0 NOT NULL, CONSTRAINT `PK_QFSO_FILES` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'updates.2013.03', 'files-create', '2.0.5', '3:38016f5f8890b4e15470c93b031d7533', 24);

--  Changeset updates.2013.03::files-indexes::Foxel::(Checksum: 3:e6cda1ab54f57fbd677bee9e143f30c5)
CREATE UNIQUE INDEX `path_sha1_idx` ON `qfso_files`(`path_sha1`);

CREATE INDEX `user_id_idx` ON `qfso_files`(`user_id`);

CREATE INDEX `acc_lvl_idx` ON `qfso_objects`(`acc_lvl`);

ALTER TABLE `qfso_files` ADD CONSTRAINT `qfso_files_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

--  INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x3), Add Foreign Key Constraint', 'EXECUTED', 'updates.2013.03', 'files-indexes', '2.0.5', '3:e6cda1ab54f57fbd677bee9e143f30c5', 25);
