<?php
/* Copyright (C) 2011-2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Image-Maps mit einfacher PHP-Unterst�tzung".
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterst�tzung" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterst�tzung" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Image-Maps mit einfacher PHP-Unterst�tzung". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/database.inc.php
 * @author Stephan Kreutzer
 * @since 2011-08-19
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
