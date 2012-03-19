-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 19, 2012 at 03:24 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mips_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `mips_analytics`
--

CREATE TABLE IF NOT EXISTS `mips_analytics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'visit unique id',
  `user_id` int(10) unsigned NOT NULL COMMENT 'user unique id',
  `device_id` int(10) unsigned NOT NULL COMMENT 'device unique id',
  `app_id` int(10) unsigned NOT NULL COMMENT 'app unique id',
  `client_ip` varchar(16) NOT NULL COMMENT 'ip v4',
  `meta_name` text NOT NULL COMMENT 'some action or event',
  `meta_content` varchar(100) NOT NULL COMMENT 'action or event data',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Auto update',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mips_analytics`
--


-- --------------------------------------------------------

--
-- Table structure for table `mips_applications`
--

CREATE TABLE IF NOT EXISTS `mips_applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'app unique id',
  `track_code` int(10) NOT NULL COMMENT 'track unique id',
  `app_name` varchar(16) NOT NULL COMMENT 'app unique name ',
  `app_version` varchar(16) NOT NULL,
  `authors` varchar(100) NOT NULL,
  `icon` varchar(32) NOT NULL COMMENT '256x256.png url in image folder',
  `screenshots` varchar(150) NOT NULL COMMENT 'array of app shots url in image folder separate by comma',
  `description` varchar(300) NOT NULL COMMENT 'app page text',
  `programming_lang` varchar(16) NOT NULL COMMENT 'web app (JS) or native (Java)',
  `core_framework_name` varchar(16) NOT NULL COMMENT 'Phonegap, Appcelarator or Native',
  `core_framework_version` varchar(16) NOT NULL COMMENT 'device.phonegap',
  `ui_framework_name` varchar(16) NOT NULL COMMENT 'jqtouch, sencha, 1js',
  `ui_framework_version` varchar(16) NOT NULL COMMENT 'jqtouch, sencha,...',
  `reg_date` datetime NOT NULL DEFAULT '1970-00-00 00:00:00' COMMENT 'On Register',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Auto update',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mips_applications`
--


-- --------------------------------------------------------

--
-- Table structure for table `mips_comments`
--

CREATE TABLE IF NOT EXISTS `mips_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'comment unique id',
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of a comment... this one is replied to',
  `user_id` int(10) unsigned NOT NULL COMMENT 'user unique id',
  `device_id` int(10) unsigned NOT NULL COMMENT 'device unique id',
  `app_id` int(10) unsigned NOT NULL COMMENT 'app unique id',
  `client_ip` varchar(16) NOT NULL COMMENT 'ip v4',
  `title` varchar(64) NOT NULL COMMENT 'comment subject',
  `comment` text NOT NULL COMMENT 'comment content',
  `rate` int(8) NOT NULL DEFAULT '0' COMMENT 'comment rank',
  `voters` varchar(128) NOT NULL COMMENT 'device unique IDs who has rated',
  `tags` varchar(32) NOT NULL DEFAULT '' COMMENT 'comment tangs id (1+3)',
  `display` int(2) NOT NULL DEFAULT '1' COMMENT 'block/show comment or not',
  `datestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'auto',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mips_comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `mips_devices`
--

CREATE TABLE IF NOT EXISTS `mips_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'device unique id',
  `user_id` int(10) unsigned NOT NULL COMMENT 'user unique id',
  `uuid` varchar(32) NOT NULL COMMENT 'device.uuid',
  `device_name` varchar(22) NOT NULL COMMENT 'device.name',
  `platform_name` varchar(12) NOT NULL COMMENT 'device.platform',
  `platform_version` varchar(12) NOT NULL COMMENT 'device.version',
  `screen_width` int(8) NOT NULL COMMENT 'screen.width',
  `screen_height` int(8) NOT NULL COMMENT 'screen.height',
  `avail_width` int(8) NOT NULL COMMENT 'screen.availWidth',
  `avail_height` int(8) NOT NULL COMMENT 'screen.availHeight',
  `color_depth` varchar(16) NOT NULL COMMENT 'screen.colorDepth',
  `user_agent` varchar(100) NOT NULL COMMENT 'navigator.userAgent',
  `language` varchar(6) NOT NULL COMMENT 'navigator.language',
  `reg_date` datetime NOT NULL DEFAULT '1970-00-00 00:00:00' COMMENT 'On Register',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Auto update',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mips_devices`
--


-- --------------------------------------------------------

--
-- Table structure for table `mips_users`
--

CREATE TABLE IF NOT EXISTS `mips_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'user unique id',
  `name` varchar(32) NOT NULL COMMENT 'Full Name',
  `nickname` varchar(16) NOT NULL COMMENT 'Display Name',
  `email` varchar(64) NOT NULL COMMENT 'Login id',
  `password` varchar(32) NOT NULL COMMENT 'Md5 pass',
  `cellphone` varchar(24) NOT NULL COMMENT 'tel',
  `reg_date` datetime NOT NULL DEFAULT '1970-00-00 00:00:00' COMMENT 'On Register',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Auto update',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mips_users`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
