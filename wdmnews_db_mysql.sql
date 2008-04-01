-- phpMyAdmin SQL Dump
-- version 2.11.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 31 Mar, 2008 at 10:38 PM
-- Versione MySQL: 3.23.32
-- Versione PHP: 5.2.5


--
-- Database: `wordpress_vergine`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `wp_wdmnews`
--

CREATE TABLE IF NOT EXISTS `wp_wdmnews` (
  `news_id` mediumint(9) NOT NULL auto_increment,
  `news` text NOT NULL,
  `data` datetime NOT NULL,
  PRIMARY KEY  (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dump dei dati per la tabella `wp_wdmnews`
--

INSERT INTO `wp_wdmnews` (`news_id`, `news`, `data`) VALUES
(5, 'Questo &egrave; il primo inserimento nel database...', '2008-03-29 22:27:19'),
(6, 'Questo &egrave; il secondo inserimento nel database, ma questo &egrave; molto pi&ugrave; bello!', '2008-03-29 22:27:41');
