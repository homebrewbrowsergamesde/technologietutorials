<?php
/* Copyright (C) 2011-2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Image-Maps mit einfacher PHP-Unterstützung".
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Image-Maps mit einfacher PHP-Unterstützung". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/ansicht_4.php
 * @author Stephan Kreutzer
 * @since 2011-08-19
 */



$session = session_start();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
     "    <head>\n".
     "        <title>Spielwelt</title>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\" />\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\" />\n".
     "    </head>\n".
     "    <body>\n";

if ($session === true)
{
    $session = isset($_SESSION['user_id']);
}

if ($session !== true)
{
    echo "        <div>\n".
         "          Bitte erst einloggen.\n".
         "        </div>\n".
         "    </body>\n".
         "</html>\n";

    exit();
}



require_once("database.inc.php");

$inventar = false;

if ($mysql_connection != false)
{
    $inventar = mysql_query("SELECT `burgschluessel`\n".
                            "FROM `inventory`\n".
                            "WHERE `user_id`=".$_SESSION['user_id']."\n",
                            $mysql_connection);
}
    
if ($inventar != false)
{
    $result = mysql_fetch_assoc($inventar);
    mysql_free_result($inventar);
    $inventar = $result;
}

if ($inventar != false)
{
    // Ansicht.

    if ($inventar['burgschluessel'] == 2)
    {
        echo "        <img src=\"ansicht_4.png\" border=\"1\" alt=\"Schatz\" />\n";
    }
    else
    {
        // Hier versucht jemand unerlaubterweise, zu gewinnen, obwohl er
        // den Schluessel noch gar nicht hat.
    }
}
else
{
    echo "        <div>\n".
         "          Problem beim Zugriff auf die Datenbank.\n".
         "        </div>\n";
}

echo "    </body>\n".
     "</html>\n".
     "\n";



?>