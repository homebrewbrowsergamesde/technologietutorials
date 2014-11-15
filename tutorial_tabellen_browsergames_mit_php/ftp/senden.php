<?php
/* Copyright (C) 2011-2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Tabellen-Browsergames mit PHP".
 *
 * Tutorial "Tabellen-Browsergames mit PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Tabellen-Browsergames mit PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Tabellen-Browsergames mit PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/senden.php
 * @author Stephan Kreutzer
 * @since 2011-12-07
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

require_once("defines.inc.php");
require_once("database.inc.php");

if ($mysql_connection != false)
{
    $gebaeude = mysql_query("SELECT `building`".
                            "FROM `building`\n".
                            "WHERE `user_id`=".$_SESSION['user_id']." AND\n".
                            "    `building`='".ENUM_GEBAEUDE_HANDELSHAUS."'",
                            $mysql_connection);
}

if ($gebaeude != false)
{
    if (mysql_fetch_assoc($gebaeude) != true)
    {
        // Hier versucht jemand unerlaubterweise, auf
        // die Seite zuzugreifen, obwohl er das Handelshaus
        // noch nicht hat.
        exit();
    }

    mysql_free_result($gebaeude);
}


echo "        <div>\n".
     "          <a href=\"uebersicht.php\">Übersicht</a>\n".
     "          <hr />\n".
     "        </div>\n";


if (isset($_POST['menge']) !== true ||
    isset($_POST['ressource']) !== true ||
    isset($_POST['empfaenger']) !== true)
{
    echo "        <form action=\"senden.php\" method=\"post\">\n".
         "          <input name=\"menge\" type=\"text\" size=\"7\" maxlength=\"7\" /> Menge.<br />\n".
         "          <select name=\"ressource\" size=\"1\">\n".
         "            <option value=\"food\">Nahrung</option>\n".
         "            <option value=\"wood\">Holz</option>\n".
         "            <option value=\"stone\">Stein</option>\n".
         "            <option value=\"coal\">Kohle</option>\n".
         "            <option value=\"iron\">Eisen</option>\n".
         "            <option value=\"gold\">Gold</option>\n".
         "          </select> Ressource.<br />\n".
         "          <input name=\"empfaenger\" type=\"text\" size=\"20\" maxlength=\"40\" /> Empfänger.<br />\n".
         "          <input type=\"submit\" value=\"Senden\" /><br />\n".
         "        </form>\n";
}
else
{
    require_once("game.inc.php");

    $result = sendResources($_SESSION['user_id'],
                            $_POST['menge'],
                            $_POST['ressource'],
                            $_POST['empfaenger'],
                            $_SESSION['user_name']);

    switch ($result)
    {
    case 0:
        echo "        <p>\n".
             "          Gesendet.\n".
             "        </p>\n";
        break;

    case -4:
        echo "        <p>\n".
             "          Empfänger existiert nicht.\n".
             "        </p>\n";
        break;

    case -6:
        echo "        <p>\n".
             "          Menge zu hoch.\n".
             "        </p>\n";
        break;

    default:
        echo "        <p>\n".
             "          Fehlgeschlagen.\n".
             "        </p>\n";
        break;
    }
}

echo "    </body>\n".
     "</html>\n".
     "\n";



?>
