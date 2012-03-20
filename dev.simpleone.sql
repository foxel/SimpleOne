-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Мар 20 2012 г., 22:02
-- Версия сервера: 5.1.58
-- Версия PHP: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `dev.simpleone`
--

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_comments`
--

DROP TABLE IF EXISTS `qfso_comments`;
CREATE TABLE IF NOT EXISTS `qfso_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `answer_to` int(10) unsigned DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `time` int(11) NOT NULL,
  `client_ip` int(10) unsigned DEFAULT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `author_id` (`author_id`),
  KEY `time` (`time`),
  KEY `client_ip` (`client_ip`),
  KEY `answer_to` (`answer_to`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_comments`:
--   `answer_to`
--       `qfso_comments` -> `id`
--   `author_id`
--       `qfso_users` -> `id`
--   `object_id`
--       `qfso_objects` -> `id`
--

--
-- Дамп данных таблицы `qfso_comments`
--

INSERT INTO `qfso_comments` (`id`, `answer_to`, `object_id`, `author_id`, `time`, `client_ip`, `text`) VALUES
(1, NULL, 1, 1, 1332255733, 3232235538, 'Test comment');

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_objects`
--

DROP TABLE IF EXISTS `qfso_objects`;
CREATE TABLE IF NOT EXISTS `qfso_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `class` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `owner_id` int(10) unsigned DEFAULT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `acc_lvl` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `acc_grp` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_lvl` tinyint(2) unsigned NOT NULL DEFAULT '3',
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
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_objects`:
--   `parent_id`
--       `qfso_objects` -> `id`
--   `owner_id`
--       `qfso_users` -> `id`
--

--
-- Дамп данных таблицы `qfso_objects`
--

INSERT INTO `qfso_objects` (`id`, `parent_id`, `class`, `owner_id`, `create_time`, `update_time`, `acc_lvl`, `acc_grp`, `edit_lvl`, `caption`, `order_id`) VALUES
(1, NULL, 'HTMLPage', 1, 1263078668, 1332254884, 0, 0, 3, 'SimpleOne', 0),
(2, 1, 'Constructor', 1, 0, 0, 6, 0, 3, 'Конструктор', 1000),
(3, 1, 'LoginPage_VKAuth', 1, 0, 0, 0, 0, 3, 'Вход / Регистрация', 5),
(4, 1, 'Poll', 1, 1332255216, 1332255301, 0, 0, 3, 'Анкета', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_objects_data`
--

DROP TABLE IF EXISTS `qfso_objects_data`;
CREATE TABLE IF NOT EXISTS `qfso_objects_data` (
  `o_id` int(10) unsigned NOT NULL,
  `data` text NOT NULL COMMENT 'serialised data',
  `change_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`o_id`),
  KEY `change_time` (`change_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_objects_data`:
--   `o_id`
--       `qfso_objects` -> `id`
--

--
-- Дамп данных таблицы `qfso_objects_data`
--

INSERT INTO `qfso_objects_data` (`o_id`, `data`, `change_time`) VALUES
(1, 'a:3:{i:0;s:51:"<p>Добро пожаловать!</p>\r\n<p>fef</p>";s:15:"commentsAllowed";b:1;s:7:"content";s:269:"<p lang="ru-RU" align="LEFT">Это тестовая страница QuickFox Simple One Alpha.</p>\r\n<p lang="ru-RU" align="LEFT">&nbsp;</p>\r\n<p lang="ru-RU" align="LEFT">Git:&nbsp;<a href="https://github.com/foxel/SimpleOne">https://github.com/foxel/SimpleOne</a></p>";}', '2012-03-20 15:02:13'),
(4, 'a:4:{s:11:"description";s:32:"<p>Пример анкеты</p>";s:9:"questions";a:2:{s:8:"7e04d928";a:4:{s:7:"caption";s:21:"Ваш возраст";s:11:"lockAnswers";b:0;s:13:"valueVariants";a:6:{s:8:"d94cf96a";s:7:"до 18";s:8:"d8fbed7e";s:7:"18 - 21";s:8:"29ef355d";s:7:"22 - 25";s:8:"2473cd70";s:7:"26 - 30";i:16717517;s:7:"30 - 40";s:8:"5109865d";s:13:"более 40";}s:11:"valueLimits";a:0:{}}s:8:"cbf7c9e3";a:4:{s:7:"caption";s:13:"Ваш пол";s:11:"lockAnswers";b:0;s:13:"valueVariants";a:2:{s:8:"286d4c21";s:15:"Мужской ";s:8:"653849b7";s:14:"Женский";}s:11:"valueLimits";a:0:{}}}s:7:"answers";a:0:{}s:11:"lockAnswers";b:0;}', '2012-03-20 14:55:01');

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_objects_navi`
--

DROP TABLE IF EXISTS `qfso_objects_navi`;
CREATE TABLE IF NOT EXISTS `qfso_objects_navi` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `path` char(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `path_hash` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `hide_in_tree` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `path_hash` (`path_hash`),
  KEY `hide_in_tree` (`hide_in_tree`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_objects_navi`:
--   `id`
--       `qfso_objects` -> `id`
--

--
-- Дамп данных таблицы `qfso_objects_navi`
--

INSERT INTO `qfso_objects_navi` (`id`, `path`, `path_hash`, `hide_in_tree`) VALUES
(1, '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(2, '/construct', 'a7b0efcfd9bdaf869cb5dff1d5bd152d', 1),
(3, '/login', '4146ec82a0f0a638db9293a0c2039e6b', 0),
(4, '/poll', '155e2a3a5b8260535c00935b25639409', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_sessions`
--

DROP TABLE IF EXISTS `qfso_sessions`;
CREATE TABLE IF NOT EXISTS `qfso_sessions` (
  `sid` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `ip` int(10) unsigned NOT NULL,
  `clsign` char(32) CHARACTER SET ascii NOT NULL,
  `starttime` int(11) NOT NULL,
  `lastused` int(11) NOT NULL,
  `clicks` int(7) unsigned NOT NULL DEFAULT '1',
  `vars` text,
  PRIMARY KEY (`sid`),
  KEY `clicks` (`clicks`),
  KEY `clsign` (`clsign`),
  KEY `ip` (`ip`),
  KEY `lastused` (`lastused`),
  KEY `starttime` (`starttime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_users`
--

DROP TABLE IF EXISTS `qfso_users`;
CREATE TABLE IF NOT EXISTS `qfso_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nick` char(16) NOT NULL DEFAULT '',
  `email` char(128) CHARACTER SET ascii NOT NULL DEFAULT '',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `mod_lvl` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `adm_lvl` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `frozen` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `readonly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `acc_group` int(5) unsigned NOT NULL DEFAULT '0',
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
  KEY `level` (`level`,`mod_lvl`,`adm_lvl`),
  KEY `sess_id` (`last_sid`),
  KEY `acc_state` (`frozen`,`readonly`),
  KEY `acc_group` (`acc_group`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `qfso_users`
--

INSERT INTO `qfso_users` (`id`, `nick`, `email`, `level`, `mod_lvl`, `adm_lvl`, `frozen`, `readonly`, `acc_group`, `avatar`, `signature`, `regtime`, `lastseen`, `last_url`, `last_uagent`, `last_ip`, `last_sid`) VALUES
(1, 'Admin', 'admin@mail.com', 7, 7, 1, 0, 0, 0, NULL, '', 1332255379, 1332255734, 'index.php', '', 3232235538, 'a9f3585d5856c83b06a0b12e44d69cc8');

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_users_auth`
--

DROP TABLE IF EXISTS `qfso_users_auth`;
CREATE TABLE IF NOT EXISTS `qfso_users_auth` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `login` char(16) CHARACTER SET ascii NOT NULL DEFAULT '',
  `pass_crypt` char(34) CHARACTER SET ascii NOT NULL,
  `pass_dropcode` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `last_auth` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `login` (`login`),
  KEY `pass_dropcode` (`pass_dropcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_users_auth`:
--   `uid`
--       `qfso_users` -> `id`
--

--
-- Дамп данных таблицы `qfso_users_auth`
--

INSERT INTO `qfso_users_auth` (`uid`, `login`, `pass_crypt`, `pass_dropcode`, `last_auth`) VALUES
(1, 'admin', '$1$e73b6648$KbSLROHwDjst.jGt/PtG90', '', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `qfso_vk_users`
--

DROP TABLE IF EXISTS `qfso_vk_users`;
CREATE TABLE IF NOT EXISTS `qfso_vk_users` (
  `uid` int(10) unsigned NOT NULL,
  `vk_id` int(10) unsigned NOT NULL,
  `token` char(64) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `vk_id` (`vk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- СВЯЗИ ТАБЛИЦЫ `qfso_vk_users`:
--   `uid`
--       `qfso_users` -> `id`
--

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `qfso_comments`
--
ALTER TABLE `qfso_comments`
  ADD CONSTRAINT `qfso_comments_ibfk_10` FOREIGN KEY (`answer_to`) REFERENCES `qfso_comments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `qfso_comments_ibfk_11` FOREIGN KEY (`author_id`) REFERENCES `qfso_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `qfso_comments_ibfk_2` FOREIGN KEY (`object_id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `qfso_objects`
--
ALTER TABLE `qfso_objects`
  ADD CONSTRAINT `qfso_objects_ibfk_8` FOREIGN KEY (`parent_id`) REFERENCES `qfso_objects` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `qfso_objects_ibfk_9` FOREIGN KEY (`owner_id`) REFERENCES `qfso_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `qfso_objects_data`
--
ALTER TABLE `qfso_objects_data`
  ADD CONSTRAINT `qfso_objects_data_ibfk_1` FOREIGN KEY (`o_id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `qfso_objects_navi`
--
ALTER TABLE `qfso_objects_navi`
  ADD CONSTRAINT `qfso_objects_navi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `qfso_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `qfso_users_auth`
--
ALTER TABLE `qfso_users_auth`
  ADD CONSTRAINT `qfso_users_auth_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `qfso_vk_users`
--
ALTER TABLE `qfso_vk_users`
  ADD CONSTRAINT `qfso_vk_users_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `qfso_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
