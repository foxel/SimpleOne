--  *********************************************************************
--  Update Database Script
--  *********************************************************************
--  Change Log: simpleone.dev.xml
--  Ran at: 17.01.15 0:27
--  Against: root@localhost@jdbc:mysql://localhost/dev.simpleone
--  Liquibase version: 2.0.5
--  *********************************************************************

--  Create Database Lock Table
CREATE TABLE `DATABASECHANGELOGLOCK` (`ID` INT NOT NULL, `LOCKED` TINYINT(1) NOT NULL, `LOCKGRANTED` DATETIME NULL, `LOCKEDBY` VARCHAR(255) NULL, CONSTRAINT `PK_DATABASECHANGELOGLOCK` PRIMARY KEY (`ID`));

INSERT INTO `DATABASECHANGELOGLOCK` (`ID`, `LOCKED`) VALUES (1, 0);

--  Lock Database
--  Create Database Change Log Table
CREATE TABLE `DATABASECHANGELOG` (`ID` VARCHAR(63) NOT NULL, `AUTHOR` VARCHAR(63) NOT NULL, `FILENAME` VARCHAR(200) NOT NULL, `DATEEXECUTED` DATETIME NOT NULL, `ORDEREXECUTED` INT NOT NULL, `EXECTYPE` VARCHAR(10) NOT NULL, `MD5SUM` VARCHAR(35) NULL, `DESCRIPTION` VARCHAR(255) NULL, `COMMENTS` VARCHAR(255) NULL, `TAG` VARCHAR(255) NULL, `LIQUIBASE` VARCHAR(20) NULL, CONSTRAINT `PK_DATABASECHANGELOG` PRIMARY KEY (`ID`, `AUTHOR`, `FILENAME`));

--  Changeset simpleone/init::init-sessions-create::Foxel::(Checksum: 3:378e627b5ee6a00c6584757a0655caed)
CREATE TABLE `qfso_sessions` (`sid` CHAR(32) DEFAULT '' NOT NULL, `ip` INT UNSIGNED NOT NULL, `clsign` CHAR(32) NOT NULL, `starttime` INT NOT NULL, `lastused` INT NOT NULL, `clicks` INT UNSIGNED DEFAULT 1 NOT NULL, `vars` LONGTEXT NULL, CONSTRAINT `PK_QFSO_SESSIONS` PRIMARY KEY (`sid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-sessions-create', '2.0.5', '3:378e627b5ee6a00c6584757a0655caed', 1);

--  Changeset simpleone/init::init-sessions-indexes::Foxel::(Checksum: 3:a1965f66b7dd0881021b3f1666a1990b)
CREATE INDEX `clicks` ON `qfso_sessions`(`clicks`);

CREATE INDEX `clsign` ON `qfso_sessions`(`clsign`);

CREATE INDEX `ip` ON `qfso_sessions`(`ip`);

CREATE INDEX `lastused` ON `qfso_sessions`(`lastused`);

CREATE INDEX `starttime` ON `qfso_sessions`(`starttime`);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x5)', 'EXECUTED', 'simpleone/init', 'init-sessions-indexes', '2.0.5', '3:a1965f66b7dd0881021b3f1666a1990b', 2);

--  Changeset simpleone/init::init-users-create::Foxel::(Checksum: 3:41d47b46edb45a89caac567d181dabe6)
CREATE TABLE `qfso_users` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `nick` CHAR(16) DEFAULT '' NOT NULL, `email` CHAR(128) CHARACTER SET ascii DEFAULT '' NOT NULL, `level` TINYINT UNSIGNED DEFAULT 1 NOT NULL, `mod_lvl` TINYINT UNSIGNED DEFAULT 0 NOT NULL, `adm_lvl` TINYINT UNSIGNED DEFAULT 0 NOT NULL, `frozen` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, `readonly` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, `acc_group` INT UNSIGNED DEFAULT 0 NOT NULL, `avatar` CHAR(128) CHARACTER SET ascii NULL, `signature` CHAR(255) DEFAULT '' NOT NULL, `regtime` INT DEFAULT 0 NOT NULL, `lastseen` INT DEFAULT 0 NOT NULL, `last_url` CHAR(255) CHARACTER SET ascii DEFAULT '' NOT NULL, `last_uagent` CHAR(255) DEFAULT '' NOT NULL, `last_ip` INT UNSIGNED DEFAULT 0 NOT NULL, `last_sid` CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, CONSTRAINT `PK_QFSO_USERS` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-users-create', '2.0.5', '3:41d47b46edb45a89caac567d181dabe6', 3);

--  Changeset simpleone/init::init-users-indexes::Foxel::(Checksum: 3:3b655f8b9e80edf46e43b13ae6d853df)
CREATE UNIQUE INDEX `nick` ON `qfso_users`(`nick`);

CREATE INDEX `email` ON `qfso_users`(`email`);

CREATE INDEX `level` ON `qfso_users`(`level`, `mod_lvl`, `adm_lvl`);

CREATE INDEX `acc_group` ON `qfso_users`(`acc_group`);

CREATE INDEX `acc_state` ON `qfso_users`(`frozen`, `readonly`);

CREATE INDEX `sess_id` ON `qfso_users`(`last_sid`);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x6)', 'EXECUTED', 'simpleone/init', 'init-users-indexes', '2.0.5', '3:3b655f8b9e80edf46e43b13ae6d853df', 4);

--  Changeset simpleone/init::init-users-insert::Foxel::(Checksum: 3:e0b6387496c39f4b91c111f165d7c0c8)
INSERT INTO `qfso_users` (`adm_lvl`, `email`, `id`, `level`, `mod_lvl`, `nick`) VALUES (7, 'admin@example.com', 1, 7, 7, 'admin');

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Insert Row', 'EXECUTED', 'simpleone/init', 'init-users-insert', '2.0.5', '3:e0b6387496c39f4b91c111f165d7c0c8', 5);

--  Changeset simpleone/init::init-users.auth-create::Foxel::(Checksum: 3:06fabbc7f96c24504f46365cb055fb7a)
CREATE TABLE `qfso_users_auth` (`uid` INT UNSIGNED DEFAULT 0 NOT NULL, `login` CHAR(16) CHARACTER SET ascii DEFAULT '' NOT NULL, `pass_crypt` CHAR(34) CHARACTER SET ascii NOT NULL, `pass_dropcode` CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, `last_auth` INT NULL, CONSTRAINT `PK_QFSO_USERS_AUTH` PRIMARY KEY (`uid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-users.auth-create', '2.0.5', '3:06fabbc7f96c24504f46365cb055fb7a', 6);

--  Changeset simpleone/init::init-users.auth-indexes::Foxel::(Checksum: 3:f57eef6d904a49cdf1dad3b247b6d61d)
CREATE UNIQUE INDEX `login` ON `qfso_users_auth`(`login`);

CREATE INDEX `pass_dropcode` ON `qfso_users_auth`(`pass_dropcode`);

ALTER TABLE `qfso_users_auth` ADD CONSTRAINT `qfso_users_auth_uid_fk` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x2), Add Foreign Key Constraint', 'EXECUTED', 'simpleone/init', 'init-users.auth-indexes', '2.0.5', '3:f57eef6d904a49cdf1dad3b247b6d61d', 7);

--  Changeset simpleone/init::init-users.auth-insert::Foxel::(Checksum: 3:1dac0342fe3ff2e81f424f0f8f09d37d)
INSERT INTO `qfso_users_auth` (`login`, `pass_crypt`, `uid`) VALUES ('admin', '$1$e73b6648$KbSLROHwDjst.jGt/PtG90', 1);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Insert Row', 'EXECUTED', 'simpleone/init', 'init-users.auth-insert', '2.0.5', '3:1dac0342fe3ff2e81f424f0f8f09d37d', 8);

--  Changeset simpleone/init::init-objects-create::Foxel::(Checksum: 3:76f3da826492a82a209c2b71385dbf00)
CREATE TABLE `qfso_objects` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `parent_id` INT UNSIGNED NULL, `class` CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, `owner_id` INT UNSIGNED NULL, `create_time` INT DEFAULT 0 NOT NULL, `update_time` INT DEFAULT 0 NOT NULL, `acc_lvl` TINYINT UNSIGNED DEFAULT 0 NOT NULL, `acc_grp` INT UNSIGNED DEFAULT 0 NOT NULL, `edit_lvl` TINYINT UNSIGNED DEFAULT 3 NOT NULL, `caption` CHAR(255) DEFAULT '' NOT NULL, `order_id` INT DEFAULT 0 NOT NULL, CONSTRAINT `PK_QFSO_OBJECTS` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-objects-create', '2.0.5', '3:76f3da826492a82a209c2b71385dbf00', 9);

--  Changeset simpleone/init::init-objects-indexes::Foxel::(Checksum: 3:5f86e2e45697f844b1232c55b75d76ea)
CREATE INDEX `class` ON `qfso_objects`(`class`);

CREATE INDEX `owner_id` ON `qfso_objects`(`owner_id`);

CREATE INDEX `time` ON `qfso_objects`(`create_time`);

CREATE INDEX `upd_time` ON `qfso_objects`(`update_time`);

CREATE INDEX `acc_lvl` ON `qfso_objects`(`acc_lvl`);

CREATE INDEX `acc_grp` ON `qfso_objects`(`acc_grp`);

CREATE INDEX `edit_lvl` ON `qfso_objects`(`edit_lvl`);

CREATE INDEX `parent_id` ON `qfso_objects`(`parent_id`);

CREATE INDEX `order_id` ON `qfso_objects`(`order_id`);

ALTER TABLE `qfso_objects` ADD CONSTRAINT `qfso_objects_owner_id_fk` FOREIGN KEY (`owner_id`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE `qfso_objects` ADD CONSTRAINT `qfso_objects_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x9), Add Foreign Key Constraint (x2)', 'EXECUTED', 'simpleone/init', 'init-objects-indexes', '2.0.5', '3:5f86e2e45697f844b1232c55b75d76ea', 10);

--  Changeset simpleone/init::init-objects.data-create::Foxel::(Checksum: 3:98a256011c193c1904b29a4c13cb0c45)
CREATE TABLE `qfso_objects_data` (`o_id` INT UNSIGNED NOT NULL, `data` LONGTEXT NOT NULL, `change_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL, CONSTRAINT `PK_QFSO_OBJECTS_DATA` PRIMARY KEY (`o_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-objects.data-create', '2.0.5', '3:98a256011c193c1904b29a4c13cb0c45', 11);

--  Changeset simpleone/init::init-objects.data-indexes::Foxel::(Checksum: 3:57ff029e434cb3adf5f9e63ff9c93475)
CREATE INDEX `change_time` ON `qfso_objects_data`(`change_time`);

ALTER TABLE `qfso_objects_data` ADD CONSTRAINT `qfso_objects_data_o_id_fk` FOREIGN KEY (`o_id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index, Add Foreign Key Constraint', 'EXECUTED', 'simpleone/init', 'init-objects.data-indexes', '2.0.5', '3:57ff029e434cb3adf5f9e63ff9c93475', 12);

--  Changeset simpleone/init::init-objects.navi-create::Foxel::(Checksum: 3:a0a8142f3affba22156e6d93972b50ad)
CREATE TABLE `qfso_objects_navi` (`id` INT UNSIGNED DEFAULT 0 NOT NULL, `path` CHAR(255) CHARACTER SET ascii DEFAULT '' NOT NULL, `path_hash` CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, `hide_in_tree` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, CONSTRAINT `PK_QFSO_OBJECTS_NAVI` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-objects.navi-create', '2.0.5', '3:a0a8142f3affba22156e6d93972b50ad', 13);

--  Changeset simpleone/init::init-objects.navi-indexes::Foxel::(Checksum: 3:f52a707c7d8896a7d24d5706f18944d0)
CREATE INDEX `path_hash` ON `qfso_objects_navi`(`path_hash`);

CREATE INDEX `hide_in_tree` ON `qfso_objects_navi`(`hide_in_tree`);

ALTER TABLE `qfso_objects_navi` ADD CONSTRAINT `qfso_objects_navi_id_fk` FOREIGN KEY (`id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x2), Add Foreign Key Constraint', 'EXECUTED', 'simpleone/init', 'init-objects.navi-indexes', '2.0.5', '3:f52a707c7d8896a7d24d5706f18944d0', 14);

--  Changeset simpleone/init::init-objects.navi-triggers::Foxel::(Checksum: 3:8b7248ae3ed3df48f71ad8e86e4ff1b0)
--  Adding triggers for qfso_objects_navi
CREATE TRIGGER qfso_objects_navi_hash_insert BEFORE INSERT ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

CREATE TRIGGER qfso_objects_navi_hash_update BEFORE UPDATE ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', 'Adding triggers for qfso_objects_navi', NOW(), 'Custom SQL', 'EXECUTED', 'simpleone/init', 'init-objects.navi-triggers', '2.0.5', '3:8b7248ae3ed3df48f71ad8e86e4ff1b0', 15);

--  Changeset simpleone/init::init-comments-create::Foxel::(Checksum: 3:b03be917534b5787055d40e5565f8b32)
CREATE TABLE `qfso_comments` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `answer_to` INT UNSIGNED NULL, `object_id` INT UNSIGNED NOT NULL, `author_id` INT UNSIGNED NULL, `time` INT NOT NULL, `client_ip` INT UNSIGNED NULL, `text` LONGTEXT NOT NULL, CONSTRAINT `PK_QFSO_COMMENTS` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-comments-create', '2.0.5', '3:b03be917534b5787055d40e5565f8b32', 16);

--  Changeset simpleone/init::init-comments-indexes::Foxel::(Checksum: 3:8a7100f3fddf7091f73142945f67b0a4)
CREATE INDEX `object_id` ON `qfso_comments`(`object_id`);

CREATE INDEX `author_id` ON `qfso_comments`(`author_id`);

CREATE INDEX `time` ON `qfso_comments`(`time`);

CREATE INDEX `client_ip` ON `qfso_comments`(`client_ip`);

CREATE INDEX `answer_to` ON `qfso_comments`(`answer_to`);

ALTER TABLE `qfso_comments` ADD CONSTRAINT `qfso_comments_answer_to_fk` FOREIGN KEY (`answer_to`) REFERENCES `qfso_comments` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `qfso_comments` ADD CONSTRAINT `qfso_comments_author_id_fk` FOREIGN KEY (`author_id`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE `qfso_comments` ADD CONSTRAINT `qfso_comments_object_id_fk` FOREIGN KEY (`object_id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x5), Add Foreign Key Constraint (x3)', 'EXECUTED', 'simpleone/init', 'init-comments-indexes', '2.0.5', '3:8a7100f3fddf7091f73142945f67b0a4', 17);

--  Changeset simpleone/init::init-tag-create::Foxel::(Checksum: 3:a4ecd4f62d59061157f26d4f0ca646b2)
CREATE TABLE `qfso_tag` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `name` VARCHAR(128) NOT NULL, `time` INT UNSIGNED NOT NULL, `user_id` INT UNSIGNED NULL, CONSTRAINT `PK_QFSO_TAG` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-tag-create', '2.0.5', '3:a4ecd4f62d59061157f26d4f0ca646b2', 18);

--  Changeset simpleone/init::init-tag-indexes::Foxel::(Checksum: 3:3cab4bdb29ad59c6e5ed1c3c9b0316f7)
CREATE UNIQUE INDEX `name` ON `qfso_tag`(`name`);

CREATE INDEX `user_id` ON `qfso_tag`(`user_id`);

CREATE INDEX `time` ON `qfso_tag`(`time`);

ALTER TABLE `qfso_tag` ADD CONSTRAINT `qfso_tag_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x3), Add Foreign Key Constraint', 'EXECUTED', 'simpleone/init', 'init-tag-indexes', '2.0.5', '3:3cab4bdb29ad59c6e5ed1c3c9b0316f7', 19);

--  Changeset simpleone/init::init-tag.object-create::Foxel::(Checksum: 3:863217e1ef25281b79b7a8a2466be8cb)
CREATE TABLE `qfso_tag_object` (`tag_id` INT UNSIGNED NOT NULL, `object_id` INT UNSIGNED NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-tag.object-create', '2.0.5', '3:863217e1ef25281b79b7a8a2466be8cb', 20);

--  Changeset simpleone/init::init-tag.object-indexes::Foxel::(Checksum: 3:a9233e063fddb41e8605af65da07b0ac)
ALTER TABLE `qfso_tag_object` ADD PRIMARY KEY (`tag_id`, `object_id`);

ALTER TABLE `qfso_tag_object` ADD CONSTRAINT `qfso_tag_object_object_id_fk` FOREIGN KEY (`object_id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `qfso_tag_object` ADD CONSTRAINT `qfso_tag_object_tag_id_fk` FOREIGN KEY (`tag_id`) REFERENCES `qfso_tag` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Add Primary Key, Add Foreign Key Constraint (x2)', 'EXECUTED', 'simpleone/init', 'init-tag.object-indexes', '2.0.5', '3:a9233e063fddb41e8605af65da07b0ac', 21);

--  Changeset simpleone/init::init-vk.users-create::Foxel::(Checksum: 3:ee3c73f3abe2d5f3f45878d1c4bb1d15)
CREATE TABLE `qfso_vk_users` (`uid` INT UNSIGNED NOT NULL, `vk_id` INT UNSIGNED NOT NULL, `token` CHAR(64) CHARACTER SET ascii NOT NULL, CONSTRAINT `PK_QFSO_VK_USERS` PRIMARY KEY (`uid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'simpleone/init', 'init-vk.users-create', '2.0.5', '3:ee3c73f3abe2d5f3f45878d1c4bb1d15', 22);

--  Changeset simpleone/init::init-vk.users-indexes::Foxel::(Checksum: 3:327da7cc307400696d2c4afc281e05a7)
CREATE UNIQUE INDEX `vk_id` ON `qfso_vk_users`(`vk_id`);

ALTER TABLE `qfso_vk_users` ADD CONSTRAINT `qfso_vk_users_uid_fk` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index, Add Foreign Key Constraint', 'EXECUTED', 'simpleone/init', 'init-vk.users-indexes', '2.0.5', '3:327da7cc307400696d2c4afc281e05a7', 23);

--  Changeset updates.2013.03::files-create::Foxel::(Checksum: 3:38016f5f8890b4e15470c93b031d7533)
CREATE TABLE `qfso_files` (`id` INT UNSIGNED AUTO_INCREMENT NOT NULL, `path_sha1` VARCHAR(40) NOT NULL, `user_id` INT UNSIGNED NOT NULL, `acc_lvl` TINYINT UNSIGNED DEFAULT 0 NOT NULL, CONSTRAINT `PK_QFSO_FILES` PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'updates.2013.03', 'files-create', '2.0.5', '3:38016f5f8890b4e15470c93b031d7533', 24);

--  Changeset updates.2013.03::files-indexes::Foxel::(Checksum: 3:e6cda1ab54f57fbd677bee9e143f30c5)
CREATE UNIQUE INDEX `path_sha1_idx` ON `qfso_files`(`path_sha1`);

CREATE INDEX `user_id_idx` ON `qfso_files`(`user_id`);

CREATE INDEX `acc_lvl_idx` ON `qfso_objects`(`acc_lvl`);

ALTER TABLE `qfso_files` ADD CONSTRAINT `qfso_files_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `qfso_users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x3), Add Foreign Key Constraint', 'EXECUTED', 'updates.2013.03', 'files-indexes', '2.0.5', '3:e6cda1ab54f57fbd677bee9e143f30c5', 25);

--  Changeset updates.2013.04::vk-oauth-rename::Foxel::(Checksum: 3:d8d3f9a053a105dc49914c25410623b2)
ALTER TABLE `qfso_vk_users` RENAME `qfso_oauth_tokens`;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Rename Table', 'EXECUTED', 'updates.2013.04', 'vk-oauth-rename', '2.0.5', '3:d8d3f9a053a105dc49914c25410623b2', 26);

--  Changeset updates.2013.04::vk-oauth-columns::Foxel::(Checksum: 3:a3ab1d78e14a65aa5b09f69d9ca75186)
ALTER TABLE `qfso_oauth_tokens` CHANGE `vk_id` `oauth_uid` CHAR(128) CHARACTER SET ascii;

ALTER TABLE `qfso_oauth_tokens` ADD `api` CHAR(8) CHARACTER SET ascii NOT NULL DEFAULT 'vk';

ALTER TABLE `qfso_oauth_tokens` CHANGE `token` `token` CHAR(255) CHARACTER SET ascii;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Rename Column, Add Column, Rename Column', 'EXECUTED', 'updates.2013.04', 'vk-oauth-columns', '2.0.5', '3:a3ab1d78e14a65aa5b09f69d9ca75186', 27);

--  Changeset updates.2013.09::users_al::Foxel::(Checksum: 3:870bc90c417aa32fb29028c7983672d6)
CREATE TABLE `qfso_users_al` (`id` CHAR(32) CHARACTER SET ascii NOT NULL, `user_id` INT UNSIGNED NULL, `user_sig` CHAR(32) CHARACTER SET ascii NOT NULL, `starttime` INT NOT NULL, `lastused` INT NULL, CONSTRAINT `PK_QFSO_USERS_AL` PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'updates.2013.09', 'users_al', '2.0.5', '3:870bc90c417aa32fb29028c7983672d6', 28);

--  Changeset updates.2013.09::init-sessions-indexes::Foxel::(Checksum: 3:d17b840d8f6bdcc80f67633b4c990a48)
CREATE INDEX `user_id` ON `qfso_users_al`(`user_id`);

CREATE INDEX `lastused` ON `qfso_users_al`(`lastused`);

CREATE INDEX `starttime` ON `qfso_users_al`(`starttime`);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Index (x3)', 'EXECUTED', 'updates.2013.09', 'init-sessions-indexes', '2.0.5', '3:d17b840d8f6bdcc80f67633b4c990a48', 29);

--  Changeset updates.2014.05::data_binary::Foxel::(Checksum: 3:78b5aa1befb35c2d7fe8dfd7aa08cb92)
ALTER TABLE qfso_objects_data MODIFY data LONGBLOB NOT NULL;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Custom SQL', 'EXECUTED', 'updates.2014.05', 'data_binary', '2.0.5', '3:78b5aa1befb35c2d7fe8dfd7aa08cb92', 30);

--  Changeset Google.db.main::create-stat-table::Foxel::(Checksum: 3:a189a6717dcb5d195fe509cd7f87f839)
CREATE TABLE `qfso_google_stats` (`period` CHAR(1) CHARACTER SET ascii NOT NULL, `object_id` INT UNSIGNED NOT NULL, `pageviews` INT UNSIGNED NOT NULL, `visitors` INT UNSIGNED NOT NULL, `visits` INT UNSIGNED NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Create Table', 'EXECUTED', 'Google.db.main', 'create-stat-table', '2.0.5', '3:a189a6717dcb5d195fe509cd7f87f839', 31);

--  Changeset Google.db.main::stat-table-constraints::Foxel::(Checksum: 3:e2127add80e7c5c110cca6f3d4782c8c)
ALTER TABLE `qfso_google_stats` ADD PRIMARY KEY (`period`, `object_id`);

INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Add Primary Key', 'EXECUTED', 'Google.db.main', 'stat-table-constraints', '2.0.5', '3:e2127add80e7c5c110cca6f3d4782c8c', 32);

