# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.44)
# Database: UserApp
# Generation Time: 2014-08-21 02:37:12 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table userapp_configuration
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_configuration`;

CREATE TABLE `userapp_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `value` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `userapp_configuration` WRITE;
/*!40000 ALTER TABLE `userapp_configuration` DISABLE KEYS */;

INSERT INTO `userapp_configuration` (`id`, `name`, `value`)
VALUES
	(1,'website_name','null'),
	(2,'website_url','null'),
	(3,'email','null'),
	(4,'activation','true'),
	(5,'resend_activation_threshold','0'),
	(6,'language','models/languages/en.php'),
	(7,'template',''),
	(8,'offline','0');

/*!40000 ALTER TABLE `userapp_configuration` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table userapp_pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_pages`;

CREATE TABLE `userapp_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(150) NOT NULL DEFAULT '',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `userapp_pages` WRITE;
/*!40000 ALTER TABLE `userapp_pages` DISABLE KEYS */;

INSERT INTO `userapp_pages` (`id`, `page`, `private`)
VALUES
	(11,'index.php',0);

/*!40000 ALTER TABLE `userapp_pages` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table userapp_permission_page_matches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_permission_page_matches`;

CREATE TABLE `userapp_permission_page_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table userapp_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_permissions`;

CREATE TABLE `userapp_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `userapp_permissions` WRITE;
/*!40000 ALTER TABLE `userapp_permissions` DISABLE KEYS */;

INSERT INTO `userapp_permissions` (`id`, `name`)
VALUES
	(1,'New Member'),
	(2,'Administrator');

/*!40000 ALTER TABLE `userapp_permissions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table userapp_user_permission_matches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_user_permission_matches`;

CREATE TABLE `userapp_user_permission_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `userapp_user_permission_matches` WRITE;
/*!40000 ALTER TABLE `userapp_user_permission_matches` DISABLE KEYS */;

INSERT INTO `userapp_user_permission_matches` (`id`, `user_id`, `permission_id`)
VALUES
	(1,1,1),
	(2,2,1),
	(3,3,1);

/*!40000 ALTER TABLE `userapp_user_permission_matches` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table userapp_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userapp_users`;

CREATE TABLE `userapp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `display_name` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(225) NOT NULL,
  `email` varchar(150) NOT NULL,
  `activation_token` varchar(225) NOT NULL,
  `last_activation_request` int(11) NOT NULL,
  `lost_password_request` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `title` varchar(150) NOT NULL,
  `sign_up_stamp` int(11) NOT NULL,
  `last_sign_in_stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
