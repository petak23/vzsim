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
(11,	3,	19,	'default,edit,edit2,add,add2,del'),
(12,	3,	13,	'default,edit,edit2,add,add2'),
(13,	3,	10,	NULL),
(14,	3,	15,	NULL),
(15,	4,	19,	'addpol'),
(16,	4,	13,	'addpol'),
(17,	4,	12,	'default'),
(18,	4,	11,	'default'),
(19,	4,	14,	'default,edit'),
(20,	4,	18,	NULL),
(21,	4,	17,	NULL),
(22,	4,	20,	NULL),
(23,	4,	16,	'default,edit'),
(24,	5,	16,	NULL),
(25,	5,	14,	NULL),
(26,	5,	11,	NULL),
(27,	5,	12,	NULL),
(28,	5,	13,	NULL),
(29,	5,	19,	NULL);

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1,	'Front:Homepage'),
(2,	'Front:User'),
(3,	'Front:UserLog'),
(4,	'Front:Edit'),
(5,	'Front:Error'),
(6,	'Front:Run'),
(7,	'Front:Clanky'),
(8,	'Front:Menu'),
(10,	'Admin:Homepage'),
(11,	'Admin:User'),
(12,	'Admin:Verzie'),
(13,	'Admin:Menu'),
(14,	'Admin:Udaje'),
(15,	'Admin:Dokumenty'),
(16,	'Admin:Lang'),
(17,	'Admin:Slider'),
(18,	'Admin:Oznam'),
(19,	'Admin:Clanky'),
(20,	'Admin:Texyla');

DROP TABLE IF EXISTS `druh`;
CREATE TABLE `druh` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Id položiek',
  `druh` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Názov druhu stredného stĺpca',
  `modul` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov špecifického modulu ak NULL vždy',
  `presenter` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Názov prezenteru pre Nette',
  `popis` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'Popis' COMMENT 'Popis bloku',
  `povolene` tinyint NOT NULL DEFAULT '1' COMMENT 'Ak 1 tak daná položka je povolená',
  `je_spec_naz` tinyint NOT NULL DEFAULT '0' COMMENT 'Ak 1 tak daný druh potrebuje špecif. názov',
  `robots` tinyint NOT NULL DEFAULT '1' COMMENT 'Ak 1 tak je povolené indexovanie daného druhu',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `druh` (`id`, `druh`, `modul`, `presenter`, `popis`, `povolene`, `je_spec_naz`, `robots`) VALUES
(1,	'clanky',	NULL,	'Clanky',	'Články - Stredná časť je ako článok, alebo je sub-menu',	1,	1,	1),
(2,	'menupol',	NULL,	'Menu',	'Položka menu - nerobí nič, len zobrazí všetky položky, ktoré sú v nej zaradené',	1,	1,	1);

DROP TABLE IF EXISTS `hlavne_menu_cast`;
CREATE TABLE `hlavne_menu_cast` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `view_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Časť' COMMENT 'Názov časti',
  `id_user_roles` int NOT NULL DEFAULT '5' COMMENT 'Id min úrovne registrácie pre editáciu',
  `mapa_stranky` tinyint NOT NULL DEFAULT '0' COMMENT 'Ak 1 tak je časť zahrnutá do mapy',
  PRIMARY KEY (`id`),
  KEY `id_registracia` (`id_user_roles`),
  CONSTRAINT `hlavne_menu_cast_ibfk_2` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Časti hlavného menu';

INSERT INTO `hlavne_menu_cast` (`id`, `view_name`, `id_user_roles`, `mapa_stranky`) VALUES
(1,	'Hlavná ponuka',	4,	1);

DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `odkaz` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Odkaz',
  `nazov` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Názov položky',
  `id_user_roles` int NOT NULL DEFAULT '4' COMMENT 'Id min úrovne registrácie',
  `avatar` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Odkaz na avatar aj s relatívnou cestou od adresára www',
  `view` tinyint NOT NULL DEFAULT '1' COMMENT 'Ak 1 položka sa zobrazí',
  PRIMARY KEY (`id`),
  KEY `id_registracia` (`id_user_roles`),
  CONSTRAINT `admin_menu_ibfk_2` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Administračné menu';

INSERT INTO `admin_menu` (`id`, `odkaz`, `nazov`, `id_user_roles`, `avatar`, `view`) VALUES
(1,	'Homepage:',	'Úvod',	3,	'Matrilineare_icon_set/png/Places user home.png',	1),
(2,	'Lang:',	'Editácia jazykov',	5,	'Matrilineare_icon_set/png/Apps gwibber.png',	1),
(3,	'Slider:',	'Editácia slider-u',	4,	'Matrilineare_icon_set/png/Places folder pictures.png',	1),
(4,	'User:',	'Editácia užívateľov',	5,	'Matrilineare_icon_set/png/Places folder publicshare.png',	1),
(5,	'Verzie:',	'Verzie webu',	4,	'Matrilineare_icon_set/png/Apps terminator.png',	1),
(6,	'Udaje:',	'Údaje webu',	4,	'Matrilineare_icon_set/png/Categories preferences desktop.png',	1);

DROP TABLE IF EXISTS `clanok_komponenty`;
CREATE TABLE `clanok_komponenty` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_hlavne_menu` int NOT NULL COMMENT 'Id hl. menu, ktorému je komponenta pripojená',
  `spec_nazov` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Špecifický názov komponenty',
  `parametre` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Parametre komponenty',
  PRIMARY KEY (`id`),
  KEY `id_clanok` (`id_hlavne_menu`),
  CONSTRAINT `clanok_komponenty_ibfk_3` FOREIGN KEY (`id_hlavne_menu`) REFERENCES `hlavne_menu` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Zoznam komponent, ktoré sú priradené k článku';


DROP TABLE IF EXISTS `clanok_lang`;
CREATE TABLE `clanok_lang` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_lang` int NOT NULL DEFAULT '1' COMMENT 'Id jazyka',
  `text` text CHARACTER SET utf8 COLLATE utf8_bin,
  `anotacia` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Anotácia článku v danom jazyku',
  PRIMARY KEY (`id`),
  KEY `id_lang` (`id_lang`),
  CONSTRAINT `clanok_lang_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Jazyková mutácia článku';

INSERT INTO `clanok_lang` (`id`, `id_lang`, `text`, `anotacia`) VALUES
(1,	1,	'Hlavná stránka...',	NULL);

DROP TABLE IF EXISTS `hlavne_menu`;
CREATE TABLE `hlavne_menu` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[5]Id položky hlavného menu',
  `spec_nazov` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov položky menu pre URL',
  `id_hlavne_menu_cast` int NOT NULL DEFAULT '1' COMMENT '[5]Ku ktorej časti hl. menu patrí položka',
  `id_user_roles` int NOT NULL DEFAULT '0' COMMENT 'Id min úrovne registrácie pre zobrazenie',
  `id_druh` int NOT NULL DEFAULT '1' COMMENT '[5]Výber druhu priradenej položky. Ak 1 tak je možné priradiť článok v náväznosti na tab. druh',
  `uroven` tinyint NOT NULL DEFAULT '0' COMMENT 'Úroveň položky menu',
  `id_nadradenej` int DEFAULT NULL COMMENT 'Id nadradenej položky menu z tejto tabuľky ',
  `id_user_main` int NOT NULL DEFAULT '1' COMMENT 'Id užívateľa',
  `poradie` int NOT NULL DEFAULT '1' COMMENT 'Poradie v zobrazení',
  `poradie_podclankov` int NOT NULL DEFAULT '0' COMMENT 'Poradie podčlánkov ak sú: 0 - od 1-9, 1 - od 9-1',
  `id_hlavne_menu_opravnenie` int NOT NULL DEFAULT '0' COMMENT 'Povolenie pre nevlastníkov (0-žiadne,1- podčlánky,2-editacia,4-všetko)',
  `zvyrazni` tinyint NOT NULL DEFAULT '0' COMMENT '[5]Zvýraznenie položky menu pri pridaní obsahu',
  `pocitadlo` int NOT NULL DEFAULT '0' COMMENT '[R]Počítadlo kliknutí na položku',
  `nazov_ul_sub` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '[5]Názov pomocnej triedy ul-elsementu sub menu',
  `id_hlavne_menu_template` int NOT NULL DEFAULT '1' COMMENT 'Vzhľad šablóny',
  `absolutna` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Absolútna adresa',
  `ikonka` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov css ikonky',
  `avatar` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov a cesta k titulnému obrázku',
  `komentar` tinyint NOT NULL DEFAULT '0' COMMENT 'Povolenie komentárov',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Posledná zmena',
  `datum_platnosti` date DEFAULT NULL COMMENT 'Platnosť',
  `aktualny_projekt` tinyint NOT NULL DEFAULT '0' COMMENT 'Označenie aktuálneho projektu',
  `redirect_id` int DEFAULT NULL COMMENT 'Id článku na ktorý sa má presmerovať',
  `id_dlzka_novinky` int NOT NULL DEFAULT '1' COMMENT 'Do kedy je to novinka',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_druh` (`id_druh`),
  KEY `id_hlavne_menu_cast` (`id_hlavne_menu_cast`),
  KEY `id_user_profiles` (`id_user_main`),
  KEY `id_dlzka_novinky` (`id_dlzka_novinky`),
  KEY `id_hlavne_menu_opravnenie` (`id_hlavne_menu_opravnenie`),
  KEY `id_hlavne_menu_template` (`id_hlavne_menu_template`),
  CONSTRAINT `hlavne_menu_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_2` FOREIGN KEY (`id_hlavne_menu_opravnenie`) REFERENCES `hlavne_menu_opravnenie` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_3` FOREIGN KEY (`id_hlavne_menu_template`) REFERENCES `hlavne_menu_template` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_4` FOREIGN KEY (`id_hlavne_menu_cast`) REFERENCES `hlavne_menu_cast` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_5` FOREIGN KEY (`id_druh`) REFERENCES `druh` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_6` FOREIGN KEY (`id_dlzka_novinky`) REFERENCES `dlzka_novinky` (`id`),
  CONSTRAINT `hlavne_menu_ibfk_7` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Položky hlavného menu';

INSERT INTO `hlavne_menu` (`id`, `spec_nazov`, `id_hlavne_menu_cast`, `id_user_roles`, `id_druh`, `uroven`, `id_nadradenej`, `id_user_main`, `poradie`, `poradie_podclankov`, `id_hlavne_menu_opravnenie`, `zvyrazni`, `pocitadlo`, `nazov_ul_sub`, `id_hlavne_menu_template`, `absolutna`, `ikonka`, `avatar`, `komentar`, `modified`, `datum_platnosti`, `aktualny_projekt`, `redirect_id`, `id_dlzka_novinky`) VALUES
(1,	'home',	1,	0,	1,	0,	NULL,	1,	1,	1,	0,	0,	0,	NULL,	1,	NULL,	NULL,	NULL,	0,	'2020-07-30 08:03:47',	NULL,	0,	NULL,	1);

DROP TABLE IF EXISTS `hlavne_menu_lang`;
CREATE TABLE `hlavne_menu_lang` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_lang` int NOT NULL DEFAULT '1' COMMENT 'Id Jazyka',
  `id_hlavne_menu` int NOT NULL COMMENT 'Id hlavného menu, ku ktorému patrí',
  `id_clanok_lang` int DEFAULT NULL COMMENT 'Id jazka článku ak ho má',
  `menu_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov položky v hlavnom menu pre daný jazyk',
  `h1part2` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Druhá časť názvu pre daný jazyk',
  `view_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Zobrazený názov položky pre daný jazyk',
  PRIMARY KEY (`id`),
  KEY `id_hlavne_menu` (`id_hlavne_menu`),
  KEY `id_lang` (`id_lang`),
  KEY `id_clanok_lang` (`id_clanok_lang`),
  CONSTRAINT `hlavne_menu_lang_ibfk_1` FOREIGN KEY (`id_hlavne_menu`) REFERENCES `hlavne_menu` (`id`),
  CONSTRAINT `hlavne_menu_lang_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `lang` (`id`),
  CONSTRAINT `hlavne_menu_lang_ibfk_3` FOREIGN KEY (`id_clanok_lang`) REFERENCES `clanok_lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Popis položiek hlavného menu pre iný jazyk';

INSERT INTO `hlavne_menu_lang` (`id`, `id_lang`, `id_hlavne_menu`, `id_clanok_lang`, `menu_name`, `h1part2`, `view_name`) VALUES
(1,	1,	1,	1,	'Hlavná stránka',	NULL,	'Hlavná stránka');

DROP TABLE IF EXISTS `hlavne_menu_opravnenie`;
CREATE TABLE `hlavne_menu_opravnenie` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `nazov` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Názov oprávnenia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Oprávnenia nevlastníkov položiek hlavného menu';

INSERT INTO `hlavne_menu_opravnenie` (`id`, `nazov`) VALUES
(0,	'Žiadne'),
(1,	'Pridávanie podčlánkov'),
(2,	'Editácia položky'),
(3,	'Pridávanie podčlánkov a editácia položky');

DROP TABLE IF EXISTS `hlavne_menu_template`;
CREATE TABLE `hlavne_menu_template` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Názov vzhľadu',
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Popis vzhľadu',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Vzhľad šablón pre položky menu';

INSERT INTO `hlavne_menu_template` (`id`, `name`, `description`) VALUES
(1,	'default',	'Základný vzhľad');

DROP TABLE IF EXISTS `dlzka_novinky`;
CREATE TABLE `dlzka_novinky` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `nazov` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Zobrazený názov',
  `dlzka` int NOT NULL COMMENT 'Počet dní, v ktorých je novinka',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tabuľka pre hodnoty dĺžky noviniek';

INSERT INTO `dlzka_novinky` (`id`, `nazov`, `dlzka`) VALUES
(1,	'Nesleduje sa',	0),
(2,	'Deň',	1),
(3,	'Týždeň',	7),
(4,	'Mesiac(30 dní)',	30),
(5,	'Štvrť roka(91 dní)',	91),
(6,	'Pol roka(182 dní)',	182),
(7,	'Rok',	365);

DROP TABLE IF EXISTS `slider`;
CREATE TABLE `slider` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `poradie` int NOT NULL DEFAULT '1' COMMENT 'Určuje poradie obrázkov v slidery',
  `nadpis` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Nadpis obrázku',
  `popis` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Popis obrázku slideru vypisovaný v dolnej časti',
  `subor` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '*.jpg' COMMENT 'Názov obrázku slideru aj s relatívnou cestou',
  `zobrazenie` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Kedy sa obrázok zobrazí',
  `id_hlavne_menu` int DEFAULT NULL COMMENT 'Odkaz na položku hlavného menu',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Popis obrázkou slideru aj s názvami súborov';

DROP TABLE IF EXISTS `verzie`;
CREATE TABLE `verzie` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_main` int(11) NOT NULL DEFAULT 1 COMMENT 'Id užívateľa',
  `cislo` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Číslo verzie',
  `subory` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Zmenené súbory',
  `text` text COLLATE utf8_bin DEFAULT NULL COMMENT 'Popis zmien',
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Dátum a čas zmeny',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cislo` (`cislo`),
  KEY `datum` (`modified`),
  KEY `id_clena` (`id_user_main`),
  CONSTRAINT `verzie_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Verzie webu';

INSERT INTO `verzie` (`id`, `id_user_main`, `cislo`, `subory`, `text`, `modified`) VALUES
(1,	1,	'0.1.0',	'Všetky',	'Prvá verzia:\n============\n- stavenie vlakových a posunových ciest\n- rušenie vlakových a posunových ciest\n- hodiny\n- celkový vzhľad\n- načítanie oblasti pre run a edit mód.\n\n',	'2021-04-16 06:33:00');

DROP TABLE IF EXISTS `dokumenty`;
CREATE TABLE `dokumenty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_hlavne_menu` int(11) NOT NULL DEFAULT 1 COMMENT 'Id položky hl. menu ku ktorej patrí',
  `id_user_main` int(11) NOT NULL DEFAULT 1 COMMENT 'Id užívateľa',
  `id_user_roles` int(11) NOT NULL DEFAULT 0 COMMENT 'Id min úrovne registrácie pre zobrazenie',
  `znacka` varchar(20) COLLATE utf8_bin DEFAULT NULL COMMENT 'Značka súboru pre vloženie do textu',
  `name` varchar(50) COLLATE utf8_bin NOT NULL COMMENT 'Názov titulku pre daný dokument',
  `pripona` varchar(20) COLLATE utf8_bin NOT NULL COMMENT 'Prípona súboru',
  `web_name` varchar(50) COLLATE utf8_bin NOT NULL COMMENT 'Špecifický názov dokumentu pre URL',
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Popis dokumentu',
  `main_file` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Názov súboru s relatívnou cestou',
  `thumb_file` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Názov súboru thumb pre obrázky a iné ',
  `change` datetime NOT NULL COMMENT 'Dátum uloženia alebo opravy - časová pečiatka',
  `zobraz_v_texte` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Zobrazenie obrázku v texte',
  `type` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Typ prílohy',
  `pocitadlo` int(11) NOT NULL DEFAULT 0 COMMENT 'Počítadlo stiahnutí',
  PRIMARY KEY (`id`),
  UNIQUE KEY `spec_nazov` (`web_name`),
  KEY `id_user_profiles` (`id_user_main`),
  KEY `id_registracia` (`id_user_roles`),
  KEY `id_hlavne_menu` (`id_hlavne_menu`),
  CONSTRAINT `dokumenty_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`),
  CONSTRAINT `dokumenty_ibfk_3` FOREIGN KEY (`id_hlavne_menu`) REFERENCES `hlavne_menu` (`id`),
  CONSTRAINT `dokumenty_ibfk_4` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Prílohy k článkom';

INSERT INTO `udaje` (`id`, `id_user_roles`, `id_udaje_typ`, `nazov`, `text`, `comment`, `separate_settings`) VALUES
(17,	5,	3,	'komentare',	'0',	'Globálne povolenie komentárov',	0);

