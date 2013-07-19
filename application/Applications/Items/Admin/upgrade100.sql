-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 20, 2013 at 07:53 PM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mycms_Zend`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_url` varchar(255) DEFAULT NULL,
  `item_status` int(11) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `images` text,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_categories`
--

CREATE TABLE IF NOT EXISTS `items_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `parents` text NOT NULL,
  `parent_id` int(11) NOT NULL,
  `childs` text NOT NULL,
  `cat_url` varchar(255) DEFAULT NULL,
  `cat_status` int(11) DEFAULT NULL,
  `cat_params` text NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_categories_lang`
--

CREATE TABLE IF NOT EXISTS `items_categories_lang` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) NOT NULL DEFAULT '0',
  `cat_name` varchar(255) DEFAULT NULL,
  `cat_desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cat_id`,`lang_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_custom_fields`
--

CREATE TABLE IF NOT EXISTS `items_custom_fields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_type` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `multi_lang` int(11) NOT NULL,
  `field_vars` text NOT NULL,
  PRIMARY KEY (`field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_custom_fields_lang`
--

CREATE TABLE IF NOT EXISTS `items_custom_fields_lang` (
  `field_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  PRIMARY KEY (`field_id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `items_fields`
--

CREATE TABLE IF NOT EXISTS `items_fields` (
  `item_id` int(11) NOT NULL,
  `field_18` varchar(255) DEFAULT NULL,
  `field_15` varchar(255) DEFAULT NULL,
  `field_20` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `items_fields_lang`
--

CREATE TABLE IF NOT EXISTS `items_fields_lang` (
  `item_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `field_18` varchar(255) DEFAULT NULL,
  `field_19` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `items_lang`
--

CREATE TABLE IF NOT EXISTS `items_lang` (
  `item_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `item_title` varchar(255) DEFAULT NULL,
  `item_content` longtext,
  PRIMARY KEY (`item_id`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
