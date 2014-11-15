-- Copyright (C) 2011-2012  Stephan Kreutzer
--
-- This file is part of Tutorial "Image-Maps mit einfacher PHP-Unterstützung".
--
-- Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as published by
-- the Free Software Foundation, version 3 of the License.
--
-- Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License
-- along with Tutorial "Image-Maps mit einfacher PHP-Unterstützung".  If not, see <http://www.gnu.org/licenses/>.



CREATE DATABASE `tutorial` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE tutorial;

CREATE USER 'tutuser'@'localhost' IDENTIFIED BY 'password';
GRANT USAGE ON *.* TO 'tutuser'@'localhost' IDENTIFIED BY 'password' WITH MAX_QUERIES_PER_HOUR 0
  MAX_CONNECTIONS_PER_HOUR 0
  MAX_UPDATES_PER_HOUR 0
  MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `tutorial`.* TO 'tutuser'@'localhost';

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `timer` (
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `zuletzt` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `timer` (`name`, `zuletzt`)
VALUES ("baum", NULL);

CREATE TABLE IF NOT EXISTS `inventory` (
  `user_id` int(11) NOT NULL,
  `aepfel` int(11) NOT NULL DEFAULT 0,
  `burgschluessel` int(11) NOT NULL DEFAULT 0,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
