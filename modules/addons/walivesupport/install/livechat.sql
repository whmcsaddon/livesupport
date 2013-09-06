SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `chat_conversations` (
  `id` int(11) NOT NULL auto_increment,
  `session` text NOT NULL,
  `user` text NOT NULL,
  `uid` int(11) NOT NULL,
  `ulevel` int(11) NOT NULL,
  `data` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `datatype` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `chat_ban` (
  `id` int(11) NOT NULL auto_increment,
  `ip` text NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `chat_notes` (
  `session` text collate utf8_unicode_ci NOT NULL,
  `timestamp` int(11) NOT NULL,
  `admin` int(11) NOT NULL,
  `note` text collate utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `chat_scripts` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `value` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `chat_scripts` (`id`, `name`, `value`, `description`) VALUES
(1, 'Promo Java', '<script>alert("hello guest")</script>', 'Sends an alert message via javascript.');

CREATE TABLE IF NOT EXISTS `chat_sessions` (
  `id` int(11) NOT NULL auto_increment,
  `session` text collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL,
  `name` text collate utf8_unicode_ci NOT NULL,
  `email` text collate utf8_unicode_ci NOT NULL,
  `question` text collate utf8_unicode_ci NOT NULL,
  `departments` text collate utf8_unicode_ci NOT NULL,
  `environment` text collate utf8_unicode_ci NOT NULL,
  `active` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ignore` text collate utf8_unicode_ci NOT NULL,
  `tid` int(11) NOT NULL,
  `utype` int(11) NOT NULL,
  `wmessage` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `chat_upload` (
  `id` int(11) NOT NULL auto_increment,
  `binary` longblob NOT NULL,
  `filename` text collate utf8_unicode_ci NOT NULL,
  `filesize` text collate utf8_unicode_ci NOT NULL,
  `filetype` text collate utf8_unicode_ci NOT NULL,
  `session` text collate utf8_unicode_ci NOT NULL,
  `uploader` int(11) NOT NULL,
  `utype` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `site_activitylogs` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `ip` text NOT NULL,
  `session` text NOT NULL,
  `pages` text NOT NULL,
  `timestamps` text NOT NULL,
  `lastaccess` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `site_script` (
  `id` int(11) NOT NULL auto_increment,
  `ip` text NOT NULL,
  `session` text NOT NULL,
  `script` text NOT NULL,
  `excuted` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `tbladminlog` ADD `online` INT NOT NULL ;

ALTER TABLE `tblticketdepartments` ADD `allowlive` INT NOT NULL ;