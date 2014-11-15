-- Copyright (C) 2012  Stephan Kreutzer
--
-- This file is part of Tutorial "Text-Browsergames mit PHP".
--
-- Tutorial "Text-Browsergames mit PHP" is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as published by
-- the Free Software Foundation, version 3 of the License.
--
-- Tutorial "Text-Browsergames mit PHP" is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License
-- along with Tutorial "Text-Browsergames mit PHP".  If not, see <http://www.gnu.org/licenses/>.



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

--
-- Daten für Tabelle `variables_global`
--

INSERT INTO `variables_global` (`variable`, `value`) VALUES
('dorf_laden_broetchen_timer', '2012-06-02'),
('dorf_laden_broetchen_anzahl', '3');

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

CREATE TABLE IF NOT EXISTS `inventory` (
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

