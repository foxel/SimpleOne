-- MySQL dump 10.13  Distrib 5.1.61, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: dev.simpleone
-- ------------------------------------------------------
-- Server version	5.1.61-0ubuntu0.11.10.1

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
-- Dumping data for table `qfso_comments`
--

LOCK TABLES `qfso_comments` WRITE;
/*!40000 ALTER TABLE `qfso_comments` DISABLE KEYS */;
INSERT INTO `qfso_comments` VALUES (1,NULL,1,1,1332255733,3232235538,'Test comment');
/*!40000 ALTER TABLE `qfso_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `qfso_objects`
--

LOCK TABLES `qfso_objects` WRITE;
/*!40000 ALTER TABLE `qfso_objects` DISABLE KEYS */;
INSERT INTO `qfso_objects` VALUES (1,NULL,'HTMLPage',1,1263078668,1332254884,0,0,3,'SimpleOne',0),(2,NULL,'Constructor',1,0,0,6,0,3,'Конструктор',1000),(3,NULL,'LoginPage',1,0,0,0,0,3,'Вход / Регистрация',5),(4,NULL,'Poll',1,1332255216,1332257955,0,0,3,'Анкета',0);
/*!40000 ALTER TABLE `qfso_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `qfso_objects_data`
--

LOCK TABLES `qfso_objects_data` WRITE;
/*!40000 ALTER TABLE `qfso_objects_data` DISABLE KEYS */;
INSERT INTO `qfso_objects_data` VALUES (1,'a:3:{i:0;s:51:\"<p>Добро пожаловать!</p>\r\n<p>fef</p>\";s:15:\"commentsAllowed\";b:1;s:7:\"content\";s:269:\"<p lang=\"ru-RU\" align=\"LEFT\">Это тестовая страница QuickFox Simple One Alpha.</p>\r\n<p lang=\"ru-RU\" align=\"LEFT\">&nbsp;</p>\r\n<p lang=\"ru-RU\" align=\"LEFT\">Git:&nbsp;<a href=\"https://github.com/foxel/SimpleOne\">https://github.com/foxel/SimpleOne</a></p>\";}','2012-03-20 15:02:13'),(4,'a:4:{s:11:\"description\";s:32:\"<p>Пример анкеты</p>\";s:9:\"questions\";a:2:{s:8:\"7e04d928\";a:4:{s:7:\"caption\";s:21:\"Ваш возраст\";s:11:\"lockAnswers\";b:0;s:13:\"valueVariants\";a:6:{s:8:\"d94cf96a\";s:7:\"до 18\";s:8:\"d8fbed7e\";s:7:\"18 - 21\";s:8:\"29ef355d\";s:7:\"22 - 25\";s:8:\"2473cd70\";s:7:\"26 - 30\";i:16717517;s:7:\"30 - 40\";s:8:\"5109865d\";s:13:\"более 40\";}s:11:\"valueLimits\";a:0:{}}s:8:\"cbf7c9e3\";a:4:{s:7:\"caption\";s:13:\"Ваш пол\";s:11:\"lockAnswers\";b:1;s:13:\"valueVariants\";a:2:{s:8:\"286d4c21\";s:15:\"Мужской \";s:8:\"653849b7\";s:14:\"Женский\";}s:11:\"valueLimits\";a:0:{}}}s:7:\"answers\";a:1:{i:2;a:2:{s:8:\"7e04d928\";s:8:\"2473cd70\";s:8:\"cbf7c9e3\";s:8:\"286d4c21\";}}s:11:\"lockAnswers\";b:0;}','2012-03-20 15:39:27');
/*!40000 ALTER TABLE `qfso_objects_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `qfso_objects_navi`
--

LOCK TABLES `qfso_objects_navi` WRITE;
/*!40000 ALTER TABLE `qfso_objects_navi` DISABLE KEYS */;
INSERT INTO `qfso_objects_navi` VALUES (1,'index','6a992d5529f459a44fee58c733255e86',0),(2,'construct','e62068a633874d298459a25f8868d306',1),(3,'login','d56b699830e77ba53855679cb1d252da',0),(4,'poll','b0f6dfb42fa80caee6825bfecd30f094',0);
/*!40000 ALTER TABLE `qfso_objects_navi` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER qfso_objects_navi_hash_insert BEFORE INSERT ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path) */;;
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
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER qfso_objects_navi_hash_update BEFORE UPDATE ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping data for table `qfso_users`
--

LOCK TABLES `qfso_users` WRITE;
/*!40000 ALTER TABLE `qfso_users` DISABLE KEYS */;
INSERT INTO `qfso_users` VALUES (1,'Admin','admin@mail.com',7,7,1,0,0,0,NULL,'',1332255379,1332828835,'construct?FSID=6ba9074e2fa336bb5868c6c2b6ea6ebf','',1541225490,'6ba9074e2fa336bb5868c6c2b6ea6ebf');
/*!40000 ALTER TABLE `qfso_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `qfso_users_auth`
--

LOCK TABLES `qfso_users_auth` WRITE;
/*!40000 ALTER TABLE `qfso_users_auth` DISABLE KEYS */;
INSERT INTO `qfso_users_auth` VALUES (1,'admin','$1$e73b6648$KbSLROHwDjst.jGt/PtG90','',NULL);
/*!40000 ALTER TABLE `qfso_users_auth` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-03-27 14:43:22
