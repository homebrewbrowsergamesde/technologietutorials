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
 * @file $/handeln.php
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



if ((isset($_POST['suche_menge']) !== true ||
     isset($_POST['suche_ressource']) !== true ||
     isset($_POST['gegen_menge']) !== true ||
     isset($_POST['gegen_ressource']) !== true) &&
    (isset($_POST['aktion']) !== true))
{
    if ($mysql_connection != false)
    {
        $trades = mysql_query("SELECT `id`,\n".
                              "    `give_amount`,\n".
                              "    `give_type`,\n".
                              "    `get_amount`,\n".
                              "    `get_type`,\n".
                              "    `user_id`\n".
                              "FROM `trading`\n".
                              "ORDER BY `time` DESC\n",
                              $mysql_connection);
    }

    if ($trades != false)
    {
        $result = array();

        while ($temp = mysql_fetch_assoc($trades))
        {
            $result[] = $temp;
        }

        mysql_free_result($trades);
        $trades = $result;
    }

    if (is_array($trades) === true)
    {
        echo "        <form action=\"handeln.php\" method=\"post\">\n".
             "          <table border=\"1\">\n".
             "            <tr>\n".
             "              <th>Biete</th>\n".
             "              <th>Gegen</th>\n".
             "              <th>Aktion</th>\n".
             "            </tr>\n";

        foreach ($trades as $trade)
        {
            echo "            <tr>\n".
                 "              <td>".$trade['give_amount']." ".translateEnumResourceToDisplayText($trade['give_type'])."</td>\n".
                 "              <td>".$trade['get_amount']." ".translateEnumResourceToDisplayText($trade['get_type'])."</td>\n";

            if ($trade['user_id'] == $_SESSION['user_id'])
            {
                echo "              <td><input type=\"radio\" name=\"aktion\" value=\"".$trade['id']."\" /> Löschen.</td>\n";
            }
            else
            {
                echo "              <td><input type=\"radio\" name=\"aktion\" value=\"".$trade['id']."\" /> Annehmen.</td>\n";
            }

            echo "            </tr>\n";
        }

        echo "            <tr>\n".
             "              <td colspan=\"3\" align=\"right\"><input type=\"submit\" value=\"Senden\" /></td>\n".
             "            </tr>\n".
             "          </table>\n".
             "        </form>\n";
    }

    echo "        <form action=\"handeln.php\" method=\"post\">\n".
         "          <p>\n".
         "            Suche:\n".
         "          </p>\n".
         "          <input name=\"suche_menge\" type=\"text\" size=\"7\" maxlength=\"7\" /> Menge.<br />\n".
         "          <select name=\"suche_ressource\" size=\"1\">\n".
         "            <option value=\"".ENUM_RESSOURCE_NAHRUNG."\">Nahrung</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_HOLZ."\">Holz</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_STEIN."\">Stein</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_KOHLE."\">Kohle</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_EISEN."\">Eisen</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_GOLD."\">Gold</option>\n".
         "          </select> Ressource.<br />\n".
         "          <p>\n".
         "            Gegen:\n".
         "          </p>\n".
         "          <input name=\"gegen_menge\" type=\"text\" size=\"7\" maxlength=\"7\" /> Menge.<br />\n".
         "          <select name=\"gegen_ressource\" size=\"1\">\n".
         "            <option value=\"".ENUM_RESSOURCE_NAHRUNG."\">Nahrung</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_HOLZ."\">Holz</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_STEIN."\">Stein</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_KOHLE."\">Kohle</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_EISEN."\">Eisen</option>\n".
         "            <option value=\"".ENUM_RESSOURCE_GOLD."\">Gold</option>\n".
         "          </select> Ressource.<br />\n".
         "          <input type=\"submit\" value=\"Senden\" /><br />\n".
         "        </form>\n";
}
else if (isset($_POST['suche_menge']) === true &&
         isset($_POST['suche_ressource']) === true &&
         isset($_POST['gegen_menge']) === true &&
         isset($_POST['gegen_ressource']) === true)
{
    require_once("game.inc.php");

    $result = placeResourceTrade($_SESSION['user_id'],
                                 $_POST['suche_menge'],
                                 $_POST['suche_ressource'],
                                 $_POST['gegen_menge'],
                                 $_POST['gegen_ressource']);

    switch ($result)
    {
    case 0:
        echo "        <p>\n".
             "          Eingetragen.\n".
             "        </p>\n";
        break;

    case -2:
        echo "        <p>\n".
             "          Gleiche Ressourcen-Art.\n".
             "        </p>\n";
        break;

    case -6:
        echo "        <p>\n".
             "          Gegen-Menge zu hoch.\n".
             "        </p>\n";
        break;

    default:
        echo "        <p>\n".
             "          Fehlgeschlagen.\n".
             "        </p>\n";
        break;
    }
}
else if (isset($_POST['aktion']) === true)
{
    require_once("game.inc.php");

    $result = handleResourceTrade($_POST['aktion'],
                                  $_SESSION['user_id']);

    switch ($result)
    {
    case 1:
        echo "        <p>\n".
             "          Gelöscht.\n".
             "        </p>\n";
        break;

    case 2:
        echo "        <p>\n".
             "          Angenommen.\n".
             "        </p>\n";
        break;

    case -9:
        echo "        <p>\n".
             "          Zu wenig Ressourcen.\n".
             "        </p>\n";
        break;

    default:
        echo "        <p>\n".
             "          Fehlgeschlagen.\n".
             "        </p>\n";
        break;
    }
}
else
{
    // Fehler in der Seiten-Logik...
}

echo "    </body>\n".
     "</html>\n".
     "\n";



?>
