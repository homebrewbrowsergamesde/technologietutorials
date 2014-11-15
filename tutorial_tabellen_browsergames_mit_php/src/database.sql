-- Copyright (C) 2011-2012  Stephan Kreutzer
--
-- This file is part of Tutorial "Tabellen-Browsergames mit PHP".
--
-- Tutorial "Tabellen-Browsergames mit PHP" is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as published by
-- the Free Software Foundation, version 3 of the License.
--
-- Tutorial "Tabellen-Browsergames mit PHP" is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License
-- along with Tutorial "Tabellen-Browsergames mit PHP".  If not, see <http://www.gnu.org/licenses/>.



CREATE DATABASE `tutorial` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE tutorial;

CREATE USER 'tutuser'@'localhost' IDENTIFIED BY 'password';
GRANT USAGE ON *.* TO 'tutuser'@'localhost' IDENTIFIED BY 'password' WITH MAX_QUERIES_PER_HOUR 0
  MAX_CONNECTIONS_PER_HOUR 0
  MAX_UPDATES_PER_HOUR 0
  MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `tutorial`.* TO 'tutuser'@'localhost';

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_bin NOT NULL,
  `salt` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `user_map` (
  `user_id` int(11) NOT NULL,
  `fields_grass` int(11) NOT NULL,
  `fields_wood` int(11) NOT NULL,
  `fields_stone` int(11) NOT NULL,
  `fields_coal` int(11) NOT NULL,
  `fields_iron` int(11) NOT NULL,
  `fields_gold` int(11) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `user_resource` (
  `user_id` int(11) NOT NULL,
  `food` int(11) NOT NULL,
  `wood` int(11) NOT NULL,
  `stone` int(11) NOT NULL,
  `coal` int(11) NOT NULL,
  `iron` int(11) NOT NULL,
  `gold` int(11) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `build_queue` (
  `user_id` int(11) NOT NULL,
  `building` ENUM('NONE', 'BAUERNHOF', 'HOLZFAELLER', 'STEINBRUCH', 'HANDELSHAUS') DEFAULT 'NONE' NOT NULL,
  `ready` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `building` (
  `user_id` int(11) NOT NULL,
  `building` ENUM('NONE', 'BAUERNHOF', 'HOLZFAELLER', 'STEINBRUCH', 'HANDELSHAUS') DEFAULT 'NONE' NOT NULL,
  `timer` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `trading` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `give_amount` int(11) NOT NULL,
  `give_type` ENUM('NONE', 'FOOD', 'WOOD', 'STONE', 'COAL', 'IRON', 'GOLD') DEFAULT 'NONE' NOT NULL,
  `get_amount` int(11) NOT NULL,
  `get_type` ENUM('NONE', 'FOOD', 'WOOD', 'STONE', 'COAL', 'IRON', 'GOLD') DEFAULT 'NONE' NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
