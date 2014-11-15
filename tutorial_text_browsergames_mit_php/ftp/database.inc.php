<?php
/* Copyright (C) 2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Text-Browsergames mit PHP".
 *
 * Tutorial "Text-Browsergames mit PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Text-Browsergames mit PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Text-Browsergames mit PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/database.inc.php
 * @brief Stellt lediglich die Datenbank-Verbindung mit fest eingetragenen
 *     Anmelde-Daten her.
 * @author Stephan Kreutzer
 * @since 2012-05-29
 */



$mysql_connection = @mysql_connect("localhost", "tutuser", "password");

if ($mysql_connection != false)
{
    if (@mysql_query("USE tutorial", $mysql_connection) == false)
    {
        @mysql_close($mysql_connection);
        $mysql_connection = false;
    }
}



?>
