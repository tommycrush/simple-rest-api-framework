-- phpMyAdmin SQL Dump
-- version 3.3.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 05, 2011 at 01:34 PM
-- Server version: 5.0.92
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `crambu_apiFramework`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_loggedin_tokens`
--

CREATE TABLE IF NOT EXISTS `api_loggedin_tokens` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_on` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `api_sessions`
--

CREATE TABLE IF NOT EXISTS `api_sessions` (
  `session_id` int(11) NOT NULL auto_increment,
  `token` varchar(50) NOT NULL,
  `token_secret` varchar(25) NOT NULL,
  `user_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `expires_on` datetime NOT NULL,
  PRIMARY KEY  (`session_id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE IF NOT EXISTS `apps` (
  `app_id` int(11) NOT NULL auto_increment,
  `app_name` varchar(100) NOT NULL,
  `app_secret` varchar(50) NOT NULL,
  PRIMARY KEY  (`app_id`),
  UNIQUE KEY `app_id` (`app_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `authed_apps`
--

CREATE TABLE IF NOT EXISTS `authed_apps` (
  `auth_id` int(11) NOT NULL auto_increment,
  `app_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  PRIMARY KEY  (`auth_id`),
  UNIQUE KEY `auth_id` (`auth_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
