SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `mysurvey_categories` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(50) NOT NULL,
  `parent`     INT(11)     NOT NULL DEFAULT '0',
  `sort_order` INT(3)      NOT NULL DEFAULT '0',
  `empty`      TINYINT(1)  NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `mysurvey_feedback` (
  `id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `avatarid`  INT(11)      NOT NULL,
  `text`      VARCHAR(200) NOT NULL,
  `userid`    INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `mysurvey_fields` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `info`       TEXT,
  `cat_id`     INT(11)      NOT NULL,
  `name`       VARCHAR(200) NOT NULL,
  `type`       INT(11)      NOT NULL,
  `sort_order` INT(11)      NOT NULL DEFAULT '0',
  `params`     VARCHAR(800) NOT NULL,
  `notes`      TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `mysurvey_news` (
  `id`        INT(11)                   NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text`      VARCHAR(300)
              COLLATE latin1_german2_ci NOT NULL,
  `userid`    INT(11)                   NOT NULL,
  `title`     VARCHAR(50)
              COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ms_sessions` (
  `user` varchar(150) NOT NULL DEFAULT '',
  `ip` varchar(150) NOT NULL DEFAULT '0',
  `session` varchar(32) NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cookie` tinyint(1) NOT NULL DEFAULT '0',
  KEY `user` (`user`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `mysurvey_survey` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `userid`      INT(11)      NOT NULL,
  `title`       VARCHAR(100) NOT NULL,
  `url`         VARCHAR(150) NOT NULL,
  `description` TEXT         NOT NULL,
  `field_1`     VARCHAR(400)          DEFAULT NULL,
  `field_2`     VARCHAR(400)          DEFAULT NULL,
  `field_3`     VARCHAR(400)          DEFAULT NULL,
  `field_4`     VARCHAR(400)          DEFAULT NULL,
  `field_5`     VARCHAR(400)          DEFAULT NULL,
  `field_8`     VARCHAR(400)          DEFAULT NULL,
  `field_9`     VARCHAR(400)          DEFAULT NULL,
  `field_10`    VARCHAR(400)          DEFAULT NULL,
  `field_11`    VARCHAR(400)          DEFAULT NULL,
  `field_12`    VARCHAR(400)          DEFAULT NULL,
  `field_13`    VARCHAR(400)          DEFAULT NULL,
  `field_14`    VARCHAR(400)          DEFAULT NULL,
  `field_15`    VARCHAR(400)          DEFAULT NULL,
  `field_16`    VARCHAR(400)          DEFAULT NULL,
  `field_17`    VARCHAR(400)          DEFAULT NULL,
  `field_18`    VARCHAR(400)          DEFAULT NULL,
  `field_19`    VARCHAR(400)          DEFAULT NULL,
  `field_20`    VARCHAR(400)          DEFAULT NULL,
  `field_21`    VARCHAR(400)          DEFAULT NULL,
  `field_22`    VARCHAR(400)          DEFAULT NULL,
  `field_23`    VARCHAR(400)          DEFAULT NULL,
  `field_24`    VARCHAR(400)          DEFAULT NULL,
  `field_25`    VARCHAR(400)          DEFAULT NULL,
  `timestamp`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments`    INT(11)      NOT NULL DEFAULT '0',
  `field_26`    VARCHAR(400)          DEFAULT NULL,
  `field_27`    VARCHAR(400)          DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ms_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userpass` varchar(60) DEFAULT NULL,
  `userlevel` int(2) DEFAULT '0',
  `usermail` varchar(80) NOT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  `firstname` varchar(55) NOT NULL,
  `lastname` varchar(55) NOT NULL,
  `change_pass` varchar(32) DEFAULT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `remember` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
