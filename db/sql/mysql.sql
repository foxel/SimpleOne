--  *********************************************************************
--  Update Database Script
--  *********************************************************************
--  Change Log: simpleone.dev.xml
--  Ran at: 19.05.15 15:36
--  Against: null@offline:mysql?version=5.0.0&outputLiquibaseSql=true
--  Liquibase version: 3.4.0-SNAPSHOT
--  *********************************************************************

CREATE TABLE DATABASECHANGELOG (ID VARCHAR(255) NOT NULL, AUTHOR VARCHAR(255) NOT NULL, FILENAME VARCHAR(255) NOT NULL, DATEEXECUTED datetime NOT NULL, ORDEREXECUTED INT NOT NULL, EXECTYPE VARCHAR(10) NOT NULL, MD5SUM VARCHAR(35) NULL, DESCRIPTION VARCHAR(255) NULL, COMMENTS VARCHAR(255) NULL, TAG VARCHAR(255) NULL, LIQUIBASE VARCHAR(20) NULL, CONTEXTS VARCHAR(255) NULL, LABELS VARCHAR(255) NULL);

--  Changeset simpleone/init::init-sessions-create::Foxel
CREATE TABLE qfso_sessions (sid CHAR(32) DEFAULT '' NOT NULL, ip INT UNSIGNED NOT NULL, clsign CHAR(32) NOT NULL, starttime INT NOT NULL, lastused INT NOT NULL, clicks INT UNSIGNED DEFAULT 1 NOT NULL, vars TEXT NULL, CONSTRAINT PK_QFSO_SESSIONS PRIMARY KEY (sid)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-sessions-create', 'Foxel', 'simpleone/init', NOW(), 1, '7:47cd3da07a447af08f37e5b583cd03c6', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-sessions-indexes::Foxel
CREATE INDEX clicks ON qfso_sessions(clicks);

CREATE INDEX clsign ON qfso_sessions(clsign);

CREATE INDEX ip ON qfso_sessions(ip);

CREATE INDEX lastused ON qfso_sessions(lastused);

CREATE INDEX starttime ON qfso_sessions(starttime);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-sessions-indexes', 'Foxel', 'simpleone/init', NOW(), 3, '7:84ba3d8362d396074941f311f03bc83a', 'createIndex (x5)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users-create::Foxel
CREATE TABLE qfso_users (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nick CHAR(16) DEFAULT '' NOT NULL, email CHAR(128) CHARACTER SET ascii DEFAULT '' NOT NULL, level TINYINT UNSIGNED DEFAULT 1 NOT NULL, mod_lvl TINYINT UNSIGNED DEFAULT 0 NOT NULL, adm_lvl TINYINT UNSIGNED DEFAULT 0 NOT NULL, frozen TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, readonly TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, acc_group INT UNSIGNED DEFAULT 0 NOT NULL, avatar CHAR(128) CHARACTER SET ascii NULL, signature CHAR(255) DEFAULT '' NOT NULL, regtime INT DEFAULT 0 NOT NULL, lastseen INT DEFAULT 0 NOT NULL, last_url CHAR(255) CHARACTER SET ascii DEFAULT '' NOT NULL, last_uagent CHAR(255) DEFAULT '' NOT NULL, last_ip INT UNSIGNED DEFAULT 0 NOT NULL, last_sid CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, CONSTRAINT PK_QFSO_USERS PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users-create', 'Foxel', 'simpleone/init', NOW(), 5, '7:2cac60f38fa62ae7998385bb326a7ae8', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users-indexes::Foxel
CREATE UNIQUE INDEX nick ON qfso_users(nick);

CREATE INDEX email ON qfso_users(email);

CREATE INDEX level ON qfso_users(level, mod_lvl, adm_lvl);

CREATE INDEX acc_group ON qfso_users(acc_group);

CREATE INDEX acc_state ON qfso_users(frozen, readonly);

CREATE INDEX sess_id ON qfso_users(last_sid);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users-indexes', 'Foxel', 'simpleone/init', NOW(), 7, '7:9dd621e00d59dcee130608776337e97b', 'createIndex (x6)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users-insert::Foxel
INSERT INTO qfso_users (id, nick, email, level, mod_lvl, adm_lvl) VALUES (1, 'admin', 'admin@example.com', 7, 7, 7);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users-insert', 'Foxel', 'simpleone/init', NOW(), 9, '7:e77dbc78df7482d111a33615e26d7b0e', 'insert', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users.auth-create::Foxel
CREATE TABLE qfso_users_auth (uid INT UNSIGNED DEFAULT 0 NOT NULL, login CHAR(16) CHARACTER SET ascii  DEFAULT '' NOT NULL, pass_crypt CHAR(34) CHARACTER SET ascii  NOT NULL, pass_dropcode CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, last_auth INT NULL, CONSTRAINT PK_QFSO_USERS_AUTH PRIMARY KEY (uid)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users.auth-create', 'Foxel', 'simpleone/init', NOW(), 11, '7:9d8023ed530abf20333a1dcde553e816', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users.auth-indexes::Foxel
CREATE UNIQUE INDEX login ON qfso_users_auth(login);

CREATE INDEX pass_dropcode ON qfso_users_auth(pass_dropcode);

ALTER TABLE qfso_users_auth ADD CONSTRAINT qfso_users_auth_uid_fk FOREIGN KEY (uid) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users.auth-indexes', 'Foxel', 'simpleone/init', NOW(), 13, '7:3555b2874f16bec1b0ae50c349fda69c', 'createIndex (x2), addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-users.auth-insert::Foxel
INSERT INTO qfso_users_auth (uid, login, pass_crypt) VALUES (1, 'admin', '$1$e73b6648$KbSLROHwDjst.jGt/PtG90');

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-users.auth-insert', 'Foxel', 'simpleone/init', NOW(), 15, '7:0915ab4b4705a7576f0e8d7951b93147', 'insert', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects-create::Foxel
CREATE TABLE qfso_objects (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_id INT UNSIGNED NULL, class CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, owner_id INT UNSIGNED NULL, create_time INT DEFAULT 0 NOT NULL, update_time INT DEFAULT 0 NOT NULL, acc_lvl TINYINT UNSIGNED DEFAULT 0 NOT NULL, acc_grp INT UNSIGNED DEFAULT 0 NOT NULL, edit_lvl TINYINT UNSIGNED DEFAULT 3 NOT NULL, caption CHAR(255) DEFAULT '' NOT NULL, order_id INT DEFAULT 0 NOT NULL, CONSTRAINT PK_QFSO_OBJECTS PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects-create', 'Foxel', 'simpleone/init', NOW(), 17, '7:d0a84f3f02d2eb2bdef22c2d6e3d26e7', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects-indexes::Foxel
CREATE INDEX class ON qfso_objects(class);

CREATE INDEX owner_id ON qfso_objects(owner_id);

CREATE INDEX time ON qfso_objects(create_time);

CREATE INDEX upd_time ON qfso_objects(update_time);

CREATE INDEX acc_lvl ON qfso_objects(acc_lvl);

CREATE INDEX acc_grp ON qfso_objects(acc_grp);

CREATE INDEX edit_lvl ON qfso_objects(edit_lvl);

CREATE INDEX parent_id ON qfso_objects(parent_id);

CREATE INDEX order_id ON qfso_objects(order_id);

ALTER TABLE qfso_objects ADD CONSTRAINT qfso_objects_owner_id_fk FOREIGN KEY (owner_id) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE qfso_objects ADD CONSTRAINT qfso_objects_parent_id_fk FOREIGN KEY (parent_id) REFERENCES qfso_objects (id) ON UPDATE CASCADE ON DELETE NO ACTION;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects-indexes', 'Foxel', 'simpleone/init', NOW(), 19, '7:03ff3693df8914b9d991c613df76f936', 'createIndex (x9), addForeignKeyConstraint (x2)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects.data-create::Foxel
CREATE TABLE qfso_objects_data (o_id INT UNSIGNED NOT NULL, data TEXT NOT NULL COMMENT 'serialised data', change_time timestamp DEFAULT NOW() NOT NULL, CONSTRAINT PK_QFSO_OBJECTS_DATA PRIMARY KEY (o_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects.data-create', 'Foxel', 'simpleone/init', NOW(), 21, '7:93affb015dc6e44ab1cfcb27e0b92090', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects.data-indexes::Foxel
CREATE INDEX change_time ON qfso_objects_data(change_time);

ALTER TABLE qfso_objects_data ADD CONSTRAINT qfso_objects_data_o_id_fk FOREIGN KEY (o_id) REFERENCES qfso_objects (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects.data-indexes', 'Foxel', 'simpleone/init', NOW(), 23, '7:60deec2d9222533c05be063e00b5da75', 'createIndex, addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects.navi-create::Foxel
CREATE TABLE qfso_objects_navi (id INT UNSIGNED DEFAULT 0 NOT NULL, path CHAR(255) CHARACTER SET ascii DEFAULT '' NOT NULL, path_hash CHAR(32) CHARACTER SET ascii DEFAULT '' NOT NULL, hide_in_tree TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL, CONSTRAINT PK_QFSO_OBJECTS_NAVI PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects.navi-create', 'Foxel', 'simpleone/init', NOW(), 25, '7:1687a56538809757b84a2a00bc3a0f25', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects.navi-indexes::Foxel
CREATE INDEX path_hash ON qfso_objects_navi(path_hash);

CREATE INDEX hide_in_tree ON qfso_objects_navi(hide_in_tree);

ALTER TABLE qfso_objects_navi ADD CONSTRAINT qfso_objects_navi_id_fk FOREIGN KEY (id) REFERENCES qfso_objects (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects.navi-indexes', 'Foxel', 'simpleone/init', NOW(), 27, '7:b91658582084941dca8c50183047ca68', 'createIndex (x2), addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-objects.navi-triggers::Foxel
--  Adding triggers for qfso_objects_navi
CREATE TRIGGER qfso_objects_navi_hash_insert BEFORE INSERT ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

CREATE TRIGGER qfso_objects_navi_hash_update BEFORE UPDATE ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-objects.navi-triggers', 'Foxel', 'simpleone/init', NOW(), 29, '7:3edddec9c50b301a8e16311b1b55fffd', 'sql', 'Adding triggers for qfso_objects_navi', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-comments-create::Foxel
CREATE TABLE qfso_comments (id INT UNSIGNED AUTO_INCREMENT NOT NULL, answer_to INT UNSIGNED NULL, object_id INT UNSIGNED NOT NULL, author_id INT UNSIGNED NULL, time INT NOT NULL, client_ip INT UNSIGNED NULL, text TEXT NOT NULL, CONSTRAINT PK_QFSO_COMMENTS PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-comments-create', 'Foxel', 'simpleone/init', NOW(), 31, '7:68f8d43fdaf796aff1e5b27dae4e702b', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-comments-indexes::Foxel
CREATE INDEX object_id ON qfso_comments(object_id);

CREATE INDEX author_id ON qfso_comments(author_id);

CREATE INDEX time ON qfso_comments(time);

CREATE INDEX client_ip ON qfso_comments(client_ip);

CREATE INDEX answer_to ON qfso_comments(answer_to);

ALTER TABLE qfso_comments ADD CONSTRAINT qfso_comments_answer_to_fk FOREIGN KEY (answer_to) REFERENCES qfso_comments (id) ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE qfso_comments ADD CONSTRAINT qfso_comments_author_id_fk FOREIGN KEY (author_id) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE qfso_comments ADD CONSTRAINT qfso_comments_object_id_fk FOREIGN KEY (object_id) REFERENCES qfso_objects (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-comments-indexes', 'Foxel', 'simpleone/init', NOW(), 33, '7:6a919caf3a7b373f3f25fc64b6ae822e', 'createIndex (x5), addForeignKeyConstraint (x3)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-tag-create::Foxel
CREATE TABLE qfso_tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, time INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL, CONSTRAINT PK_QFSO_TAG PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-tag-create', 'Foxel', 'simpleone/init', NOW(), 35, '7:506df67d6104d3d89722f37990a6c428', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-tag-indexes::Foxel
CREATE UNIQUE INDEX name ON qfso_tag(name);

CREATE INDEX user_id ON qfso_tag(user_id);

CREATE INDEX time ON qfso_tag(time);

ALTER TABLE qfso_tag ADD CONSTRAINT qfso_tag_user_id_fk FOREIGN KEY (user_id) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE SET NULL;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-tag-indexes', 'Foxel', 'simpleone/init', NOW(), 37, '7:8fedcd0488a9d5be7086b334a24a1ec1', 'createIndex (x3), addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-tag.object-create::Foxel
CREATE TABLE qfso_tag_object (tag_id INT UNSIGNED NOT NULL, object_id INT UNSIGNED NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-tag.object-create', 'Foxel', 'simpleone/init', NOW(), 39, '7:2b0f539dcf6fb2fa48c4d37f64ea2a07', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-tag.object-indexes::Foxel
ALTER TABLE qfso_tag_object ADD PRIMARY KEY (tag_id, object_id);

ALTER TABLE qfso_tag_object ADD CONSTRAINT qfso_tag_object_object_id_fk FOREIGN KEY (object_id) REFERENCES qfso_objects (id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE qfso_tag_object ADD CONSTRAINT qfso_tag_object_tag_id_fk FOREIGN KEY (tag_id) REFERENCES qfso_tag (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-tag.object-indexes', 'Foxel', 'simpleone/init', NOW(), 41, '7:8d3b21f4fb256dbc0b370694eec2f3b6', 'addPrimaryKey, addForeignKeyConstraint (x2)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-vk.users-create::Foxel
CREATE TABLE qfso_vk_users (uid INT UNSIGNED NOT NULL, vk_id INT UNSIGNED NOT NULL, token CHAR(64) CHARACTER SET ascii NOT NULL, CONSTRAINT PK_QFSO_VK_USERS PRIMARY KEY (uid)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-vk.users-create', 'Foxel', 'simpleone/init', NOW(), 43, '7:f502ebd4b39e0f58dc53552a5f028034', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset simpleone/init::init-vk.users-indexes::Foxel
CREATE UNIQUE INDEX vk_id ON qfso_vk_users(vk_id);

ALTER TABLE qfso_vk_users ADD CONSTRAINT qfso_vk_users_uid_fk FOREIGN KEY (uid) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-vk.users-indexes', 'Foxel', 'simpleone/init', NOW(), 45, '7:91afdd3ba2312e2a8a61d92cf226a4d4', 'createIndex, addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.03::files-create::Foxel
CREATE TABLE qfso_files (id INT UNSIGNED AUTO_INCREMENT NOT NULL, path_sha1 VARCHAR(40) NOT NULL, user_id INT UNSIGNED NOT NULL, acc_lvl TINYINT UNSIGNED DEFAULT 0 NOT NULL, CONSTRAINT PK_QFSO_FILES PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('files-create', 'Foxel', 'updates.2013.03', NOW(), 47, '7:975d889bde43bf2a2c2e3e5e15ffcd76', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.03::files-indexes::Foxel
CREATE UNIQUE INDEX path_sha1_idx ON qfso_files(path_sha1);

CREATE INDEX user_id_idx ON qfso_files(user_id);

CREATE INDEX acc_lvl_idx ON qfso_objects(acc_lvl);

ALTER TABLE qfso_files ADD CONSTRAINT qfso_files_user_id_fk FOREIGN KEY (user_id) REFERENCES qfso_users (id) ON UPDATE CASCADE ON DELETE CASCADE;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('files-indexes', 'Foxel', 'updates.2013.03', NOW(), 49, '7:e0befdea2c16e52e4843258debeb3943', 'createIndex (x3), addForeignKeyConstraint', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.04::vk-oauth-rename::Foxel
ALTER TABLE qfso_vk_users RENAME qfso_oauth_tokens;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('vk-oauth-rename', 'Foxel', 'updates.2013.04', NOW(), 51, '7:1703ad8892a9f0afc94c2cdffa197eaf', 'renameTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.04::vk-oauth-columns::Foxel
ALTER TABLE qfso_oauth_tokens CHANGE vk_id oauth_uid CHAR(128) CHARACTER SET ascii;

ALTER TABLE qfso_oauth_tokens ADD api CHAR(8) CHARACTER SET ascii NOT NULL DEFAULT 'vk';

ALTER TABLE qfso_oauth_tokens CHANGE token token CHAR(255) CHARACTER SET ascii;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('vk-oauth-columns', 'Foxel', 'updates.2013.04', NOW(), 53, '7:8685b5ba3d10bfad91f46f63880355d4', 'renameColumn, addColumn, renameColumn', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.09::users_al::Foxel
CREATE TABLE qfso_users_al (id CHAR(32) CHARACTER SET ascii NOT NULL, user_id INT UNSIGNED NULL, user_sig CHAR(32) CHARACTER SET ascii NOT NULL, starttime INT NOT NULL, lastused INT NULL, CONSTRAINT PK_QFSO_USERS_AL PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('users_al', 'Foxel', 'updates.2013.09', NOW(), 55, '7:3c6f2bb4959b8b7c727995abcdc6845f', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2013.09::init-sessions-indexes::Foxel
CREATE INDEX user_id ON qfso_users_al(user_id);

CREATE INDEX lastused ON qfso_users_al(lastused);

CREATE INDEX starttime ON qfso_users_al(starttime);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('init-sessions-indexes', 'Foxel', 'updates.2013.09', NOW(), 57, '7:30c40e53cb93b03bec362ff9d8c960b9', 'createIndex (x3)', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset updates.2014.05::data_binary::Foxel
ALTER TABLE qfso_objects_data MODIFY data LONGBLOB NOT NULL;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('data_binary', 'Foxel', 'updates.2014.05', NOW(), 59, '7:09a4f9bca39cbda3c79f52f2038d3457', 'sql', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset Google.db.main::create-stat-table::Foxel
CREATE TABLE qfso_google_stats (period CHAR(1) CHARACTER SET ascii NOT NULL, object_id INT UNSIGNED NOT NULL, pageviews INT UNSIGNED NOT NULL, visitors INT UNSIGNED NOT NULL, visits INT UNSIGNED NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('create-stat-table', 'Foxel', 'Google.db.main', NOW(), 61, '7:9c9f91ebf6a93601adc91ff45816742d', 'createTable', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

--  Changeset Google.db.main::stat-table-constraints::Foxel
ALTER TABLE qfso_google_stats ADD PRIMARY KEY (period, object_id);

INSERT INTO DATABASECHANGELOG (ID, AUTHOR, FILENAME, DATEEXECUTED, ORDEREXECUTED, MD5SUM, DESCRIPTION, COMMENTS, EXECTYPE, CONTEXTS, LABELS, LIQUIBASE) VALUES ('stat-table-constraints', 'Foxel', 'Google.db.main', NOW(), 63, '7:e65c77622d9de30da54a74ec56fc4a0b', 'addPrimaryKey', '', 'EXECUTED', NULL, NULL, '3.4.0-SNP');

