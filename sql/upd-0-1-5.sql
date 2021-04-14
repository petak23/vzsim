UPDATE `oblast_prvky` SET `c2` = '1158' WHERE `id` = '347';

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_roles` int(11) NOT NULL DEFAULT 0 COMMENT 'Užívateľská rola',
  `id_user_resource` int(11) NOT NULL COMMENT 'Zdroj',
  `actions` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Povolenie na akciu. (Ak viac oddelené čiarkou, ak null tak všetko)',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_user_resource` (`id_user_resource`),
  CONSTRAINT `user_permission_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `user_permission_ibfk_2` FOREIGN KEY (`id_user_resource`) REFERENCES `user_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Užívateľské oprávnenia';

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `name` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'Názov zdroja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Zdroje oprávnení';

INSERT INTO `user_permission` (`id`, `id_user_roles`, `id_user_resource`, `actions`) VALUES
(1,	0,	1,	NULL),
(2,	0,	2,	NULL),
(3,	1,	3,	NULL),
(4,	1,	4,	NULL),
(5,	0,	5,	NULL),
(6,	1,	6,	NULL),
(7,	0,	7,	NULL),
(8,	0,	8,	NULL),
(9,	4,	9,	NULL),
(10,	4,	10,	NULL),
(11,	4,	10,	NULL),
(12,	4,	10,	NULL),
(13,	4,	10,	NULL),
(14,	4,	10,	NULL),
(15,	4,	10,	NULL),
(16,	4,	10,	NULL),
(17,	4,	10,	NULL);

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1,	'Front:Homepage'),
(2,	'Front:User'),
(3,	'Front:UserLog'),
(4,	'Front:Edit'),
(5,	'Front:Error'),
(6,	'Front:Run'),
(7,	'Front:Clanky'),
(8,	'Front:Menu'),
(9,	'Admin:Homepage'),
(10,	'Admin:User'),
(11,	'Admin:Verzie'),
(12,	'Admin:Menu'),
(13,	'Admin:Udaje'),
(14,	'Admin:Lang'),
(15,	'Admin:Slider'),
(16,	'Admin:Clanky'),
(17,	'Admin:Texyla');