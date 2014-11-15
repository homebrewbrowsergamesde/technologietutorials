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
 * @file $/ansicht_1.php
 * @author Stephan Kreutzer
 * @since 2011-08-02
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

    echo "        <img src=\"ansicht_1.png\" border=\"1\" alt=\"Burg\" usemap=\"#default\"/>\n".
         "        <map name=\"default\">\n".
         //         Bewegen geht immer.
         "          <area shape=\"poly\" coords=\"799,599,767,599,767,356,799,371\" href=\"ansicht_2.php\" alt=\"Rechts entlang\" title=\"Rechts entlang\" />\n".
         "          <area shape=\"poly\" coords=\"0,383,38,394,80,399,80,599,0,599\" href=\"ansicht_3.php\" alt=\"Links entlang\" title=\"Links entlang\" />\n";

    if ($inventar['burgschluessel'] == 2)
    {
        echo "          <area shape=\"poly\" coords=\"635,325,635,288,653,281,664,281,681,290,681,331,635,325\" href=\"ansicht_4.php\" alt=\"Burgtor\" title=\"Schlüssel benutzen\" />\n";
    }

    echo "        </map>\n";
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