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
 * @file $/bauen.php
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

echo "        <div>\n".
     "          <a href=\"uebersicht.php\">Übersicht</a>\n".
     "          <hr />\n".
     "        </div>\n";


require_once("defines.inc.php");
require_once("database.inc.php");

$map = false;

if ($mysql_connection != false)
{
    $map = mysql_query("SELECT `fields_grass`,".
                       "    `fields_wood`,\n".
                       "    `fields_stone`,\n".
                       "    `fields_coal`,\n".
                       "    `fields_iron`,\n".
                       "    `fields_gold`\n".
                       "FROM `user_map`\n".
                       "WHERE `user_id`=".$_SESSION['user_id']."\n",
                       $mysql_connection);
}

if ($map != false)
{
    $result = mysql_fetch_assoc($map);
    mysql_free_result($map);
    $map = $result;
}

// TODO: Evtl. mit COUNT...
$gebaeude = false;

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
    $result = array();

    while ($temp = mysql_fetch_assoc($gebaeude))
    {
        $result[] = $temp;
    }

    mysql_free_result($gebaeude);
    $gebaeude = $result;
}

$bauschlange = false;

if ($mysql_connection != false)
{
    $bauschlange = mysql_query("SELECT `building`".
                               "FROM `build_queue`\n".
                               "WHERE `user_id`=".$_SESSION['user_id']." AND\n".
                               "    `building`='".ENUM_GEBAEUDE_HANDELSHAUS."'",
                               $mysql_connection);
}

if ($bauschlange!= false)
{
    $result = array();

    while ($temp = mysql_fetch_assoc($bauschlange))
    {
        $result[] = $temp;
    }

    mysql_free_result($bauschlange);
    $bauschlange = $result;
}


if (isset($_POST['gebaeude']) !== true)
{
    echo "        <p>\n".
         "          Reihenfolge der Angaben (Dauer &ndash; Baukosten &ndash; Produktion &ndash; Betriebskosten).\n".
         "        </p>\n".
         "        <form action=\"bauen.php\" method=\"post\">\n".
         "          <p>\n".
         "            Land:\n".
         "          </p>\n";

    if (is_array($map) === true)
    {
        if ((int) $map['fields_grass'] > 0)
        {
            echo "          <input type=\"radio\" name=\"gebaeude\" value=\"".ENUM_GEBAEUDE_BAUERNHOF."\" /> Bauernhof (3 Tage &ndash; 3 Holz &ndash; 2 Nahrung &ndash; nichts).<br />\n";
        }

        if ((int) $map['fields_wood'] > 0)
        {
            echo "          <input type=\"radio\" name=\"gebaeude\" value=\"".ENUM_GEBAEUDE_HOLZFAELLER."\" /> Holzfäller (1 Tag &ndash; 1 Holz &ndash; 1 Holz &ndash; 1 Nahrung).<br />\n";
        }

        if ((int) $map['fields_stone'] > 0)
        {
            echo "          <input type=\"radio\" name=\"gebaeude\" value=\"".ENUM_GEBAEUDE_STEINBRUCH."\" /> Steinbruch (2 Tage &ndash; 2 Holz &ndash; 1 Stein &ndash; 1 Nahrung).<br />\n";
        }
    }

    echo "          <p>\n".
         "            Stadt:\n".
         "          </p>\n";

    if (is_array($gebaeude) === true &&
        is_array($bauschlange) === true)
    {
        $handelshausAnbieten = true;

        foreach ($gebaeude as $g)
        {
            if ($g['building'] == ENUM_GEBAEUDE_HANDELSHAUS)
            {
               $handelshausAnbieten = false;
               break;
            }
        }

        foreach ($bauschlange as $b)
        {
            if ($b['building'] == ENUM_GEBAEUDE_HANDELSHAUS)// ||
            //    $handelshausAnbieten == false)
            {
                $handelshausAnbieten = false;
                break;
            }
        }

        if ($handelshausAnbieten === true)
        {
            echo "          <input type=\"radio\" name=\"gebaeude\" value=\"".ENUM_GEBAEUDE_HANDELSHAUS."\" /> Handelshaus (4 Tage &ndash; 5 Holz, 5 Stein &ndash; Handel &ndash; nichts).<br />\n";
        }
    }

    echo "          <input type=\"submit\" value=\"Senden\" /><br />\n".
         "        </form>\n";
}
else
{
    require_once("game.inc.php");

    $result = insertNewBuilding($_SESSION['user_id'], $_POST['gebaeude']);

    switch ($result)
    {
    case 0:
        echo "        <p>\n".
             "          Wird gebaut...\n".
             "        </p>\n";
        break;

    case -6:
        echo "        <p>\n".
             "          Zu wenig Holz.\n".
             "        </p>\n";
        break;

    case -7:
        echo "        <p>\n".
             "          Zu wenig Stein.\n".
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
