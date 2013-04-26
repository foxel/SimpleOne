--  Changeset updates.2013.04::vk-oauth-rename::Foxel::(Checksum: 3:d8d3f9a053a105dc49914c25410623b2)
ALTER TABLE `qfso_vk_users` RENAME `qfso_oauth_tokens`;

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Rename Table', 'EXECUTED', 'updates.2013.04', 'vk-oauth-rename', '2.0.5', '3:d8d3f9a053a105dc49914c25410623b2', 26);

--  Changeset updates.2013.04::vk-oauth-columns::Foxel::(Checksum: 3:a3ab1d78e14a65aa5b09f69d9ca75186)
ALTER TABLE `qfso_oauth_tokens` CHANGE `vk_id` `oauth_uid` CHAR(128) CHARACTER SET ascii;

ALTER TABLE `qfso_oauth_tokens` ADD `api` CHAR(8) CHARACTER SET ascii NOT NULL DEFAULT 'vk';

ALTER TABLE `qfso_oauth_tokens` CHANGE `token` `token` CHAR(255) CHARACTER SET ascii;

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Rename Column, Add Column, Rename Column', 'EXECUTED', 'updates.2013.04', 'vk-oauth-columns', '2.0.5', '3:a3ab1d78e14a65aa5b09f69d9ca75186', 27);
