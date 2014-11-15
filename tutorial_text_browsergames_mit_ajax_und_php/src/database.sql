-- Copyright (C) 2012  Stephan Kreutzer
--
-- This file is part of Tutorial "Text-Browsergames mit AJAX und PHP".
--
-- Tutorial "Text-Browsergames mit AJAX und PHP" is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License version 3 only,
-- as published by the Free Software Foundation.
--
-- Tutorial "Text-Browsergames mit AJAX und PHP" is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
-- GNU Affero General Public License 3 for more details.
--
-- You should have received a copy of the GNU Affero General Public License 3
-- along with Tutorial "Text-Browsergames mit AJAX und PHP". If not, see <http://www.gnu.org/licenses/>.



CREATE DATABASE `tutorial` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE tutorial;

CREATE USER 'tutuser'@'localhost' IDENTIFIED BY 'password';
GRANT USAGE ON *.* TO 'tutuser'@'localhost' IDENTIFIED BY 'password' WITH MAX_QUERIES_PER_HOUR 0
  MAX_CONNECTIONS_PER_HOUR 0
  MAX_UPDATES_PER_HOUR 0
  MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `tutorial`.* TO 'tutuser'@'localhost';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_bin NOT NULL,
  `salt` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `scene` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `variables_global`
--

CREATE TABLE IF NOT EXISTS `variables_global` (
  `variable` varchar(255) COLLATE utf8_bin NOT NULL,
  `value` varchar(255) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `name` (`variable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO `variables_global` (`variable`, `value`) VALUES ('bank_geldsumme', '15');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `variables_user`
--

CREATE TABLE IF NOT EXISTS `variables_user` (
  `variable` varchar(255) COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_bin NOT NULL,
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `inventory`
--

CREATE TABLE IF NOT EXISTS `inventory` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

