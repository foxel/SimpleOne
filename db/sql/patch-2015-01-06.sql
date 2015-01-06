--  *********************************************************************
--  Update Database Script
--  *********************************************************************
--  Change Log: simpleone.dev.xml
--  Ran at: 06.01.15 16:15
--  Against: root@localhost@jdbc:mysql://localhost/dev.simpleone
--  Liquibase version: 2.0.5
--  *********************************************************************

--  Lock Database
--  Changeset Google.db.main::create-stat-table::Foxel::(Checksum: 3:a189a6717dcb5d195fe509cd7f87f839)
CREATE TABLE `qfso_google_stats` (`period` CHAR(1) CHARACTER SET ascii NOT NULL, `object_id` INT UNSIGNED NOT NULL, `pageviews` INT UNSIGNED NOT NULL, `visitors` INT UNSIGNED NOT NULL, `visits` INT UNSIGNED NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'Google.db.main', 'create-stat-table', '2.0.5', '3:a189a6717dcb5d195fe509cd7f87f839', 31);

--  Changeset Google.db.main::stat-table-constraints::Foxel::(Checksum: 3:e2127add80e7c5c110cca6f3d4782c8c)
ALTER TABLE `qfso_google_stats` ADD PRIMARY KEY (`period`, `object_id`);

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Add Primary Key', 'EXECUTED', 'Google.db.main', 'stat-table-constraints', '2.0.5', '3:e2127add80e7c5c110cca6f3d4782c8c', 32);

--  Release Database Lock
