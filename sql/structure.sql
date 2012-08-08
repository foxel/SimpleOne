-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: dev.simpleone
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `qfso_comments`
--

DROP TABLE IF EXISTS `qfso_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `answer_to` int(10) unsigned DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `time` int(11) NOT NULL,
  `client_ip` int(10) unsigned DEFAULT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `author_id` (`author_id`),
  KEY `time` (`time`),
  KEY `client_ip` (`client_ip`),
  KEY `answer_to` (`answer_to`),
  CONSTRAINT `qfso_comments_object_id_fk` FOREIGN KEY (`object_id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `qfso_comments_answer_to_fk` FOREIGN KEY (`answer_to`) REFERENCES `qfso_comments` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `qfso_comments_author_id_fk` FOREIGN KEY (`author_id`) REFERENCES `qfso_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_objects`
--

DROP TABLE IF EXISTS `qfso_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `class` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `owner_id` int(10) unsigned DEFAULT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `acc_lvl` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `acc_grp` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_lvl` tinyint(3) unsigned NOT NULL DEFAULT '3',
  `caption` char(255) NOT NULL DEFAULT '',
  `order_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `class` (`class`),
  KEY `owner_id` (`owner_id`),
  KEY `time` (`create_time`),
  KEY `upd_time` (`update_time`),
  KEY `acc_lvl` (`acc_lvl`),
  KEY `acc_grp` (`acc_grp`),
  KEY `edit_lvl` (`edit_lvl`),
  KEY `parent_id` (`parent_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `qfso_objects_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `qfso_objects` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `qfso_objects_owner_id_fk` FOREIGN KEY (`owner_id`) REFERENCES `qfso_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_objects_data`
--

DROP TABLE IF EXISTS `qfso_objects_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_objects_data` (
  `o_id` int(10) unsigned NOT NULL,
  `data` longtext NOT NULL,
  `change_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`o_id`),
  KEY `change_time` (`change_time`),
  CONSTRAINT `qfso_objects_data_o_id_fk` FOREIGN KEY (`o_id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_objects_navi`
--

DROP TABLE IF EXISTS `qfso_objects_navi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_objects_navi` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `path` char(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `path_hash` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `hide_in_tree` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `path_hash` (`path_hash`),
  KEY `hide_in_tree` (`hide_in_tree`),
  CONSTRAINT `qfso_objects_navi_id_fk` FOREIGN KEY (`id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER qfso_objects_navi_hash_insert BEFORE INSERT ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER qfso_objects_navi_hash_update BEFORE UPDATE ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `qfso_sessions`
--

DROP TABLE IF EXISTS `qfso_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_sessions` (
  `sid` char(32) NOT NULL DEFAULT '',
  `ip` int(10) unsigned NOT NULL,
  `clsign` char(32) NOT NULL,
  `starttime` int(11) NOT NULL,
  `lastused` int(11) NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '1',
  `vars` longtext,
  PRIMARY KEY (`sid`),
  KEY `clicks` (`clicks`),
  KEY `clsign` (`clsign`),
  KEY `ip` (`ip`),
  KEY `lastused` (`lastused`),
  KEY `starttime` (`starttime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_users`
--

DROP TABLE IF EXISTS `qfso_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nick` char(16) NOT NULL DEFAULT '',
  `email` char(128) CHARACTER SET ascii NOT NULL DEFAULT '',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `mod_lvl` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `adm_lvl` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `frozen` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `readonly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `acc_group` int(10) unsigned NOT NULL DEFAULT '0',
  `avatar` char(128) CHARACTER SET ascii DEFAULT NULL,
  `signature` char(255) NOT NULL DEFAULT '',
  `regtime` int(11) NOT NULL DEFAULT '0',
  `lastseen` int(11) NOT NULL DEFAULT '0',
  `last_url` char(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `last_uagent` char(255) NOT NULL DEFAULT '',
  `last_ip` int(10) unsigned NOT NULL DEFAULT '0',
  `last_sid` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`),
  KEY `email` (`email`),
  KEY `level` (`level`,`mod_lvl`,`adm_lvl`),
  KEY `acc_group` (`acc_group`),
  KEY `acc_state` (`frozen`,`readonly`),
  KEY `sess_id` (`last_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_users_auth`
--

DROP TABLE IF EXISTS `qfso_users_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_users_auth` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `login` char(16) CHARACTER SET ascii NOT NULL DEFAULT '',
  `pass_crypt` char(34) CHARACTER SET ascii NOT NULL,
  `pass_dropcode` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `last_auth` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `login` (`login`),
  KEY `pass_dropcode` (`pass_dropcode`),
  CONSTRAINT `qfso_users_auth_uid_fk` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_vk_users`
--

DROP TABLE IF EXISTS `qfso_vk_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_vk_users` (
  `uid` int(10) unsigned NOT NULL,
  `vk_id` int(10) unsigned NOT NULL,
  `token` char(64) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `vk_id` (`vk_id`),
  CONSTRAINT `qfso_vk_users_uid_fk` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_tag`
--

DROP TABLE IF EXISTS `qfso_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `user_id` (`user_id`),
  KEY `time` (`time`),
  CONSTRAINT `qfso_tag_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `qfso_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qfso_tag_object`
--

DROP TABLE IF EXISTS `qfso_tag_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qfso_tag_object` (
  `tag_id` int(10) unsigned NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`object_id`),
  KEY `qfso_tag_object_object_id_fk` (`object_id`),
  CONSTRAINT `qfso_tag_object_tag_id_fk` FOREIGN KEY (`tag_id`) REFERENCES `qfso_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `qfso_tag_object_object_id_fk` FOREIGN KEY (`object_id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-08-08 14:30:07
