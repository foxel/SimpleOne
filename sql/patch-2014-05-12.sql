--  Changeset updates.2014.05::data_binary::Foxel::(Checksum: 3:78b5aa1befb35c2d7fe8dfd7aa08cb92)
ALTER TABLE qfso_objects_data MODIFY data LONGBLOB NOT NULL;

-- INSERT INTO `DATABASECHANGELOG` (`AUTHOR`, `COMMENTS`, `DATEEXECUTED`, `DESCRIPTION`, `EXECTYPE`, `FILENAME`, `ID`, `LIQUIBASE`, `MD5SUM`, `ORDEREXECUTED`) VALUES ('Foxel', '', NOW(), 'Custom SQL', 'EXECUTED', 'updates.2014.05', 'data_binary', '2.0.5', '3:78b5aa1befb35c2d7fe8dfd7aa08cb92', 30);

