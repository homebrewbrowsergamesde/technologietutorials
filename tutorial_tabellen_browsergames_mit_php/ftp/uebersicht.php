<?php
/* Copyright (C) 2011-2017  Stephan Kreutzer
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
 * @file $/uebersicht.php
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

$ressourcen = false;

if ($mysql_connection != false)
{
    $ressourcen = mysqli_query($mysql_connection,
                               "SELECT `food`,".
                               "    `wood`,\n".
                               "    `stone`,\n".
                               "    `coal`,\n".
                               "    `iron`,\n".
                               "    `gold`\n".
                               "FROM `user_resource`\n".
                               "WHERE `user_id`=".$_SESSION['user_id']."\n");
}

if ($ressourcen != false)
{
    $result = mysqli_fetch_assoc($ressourcen);
    mysqli_free_result($ressourcen);
    $ressourcen = $result;
}

$map = false;

if ($mysql_connection != false)
{
    $map = mysqli_query($mysql_connection,
                        "SELECT `fields_grass`,".
                        "    `fields_wood`,\n".
                        "    `fields_stone`,\n".
                        "    `fields_coal`,\n".
                        "    `fields_iron`,\n".
                        "    `fields_gold`\n".
                        "FROM `user_map`\n".
                        "WHERE `user_id`=".$_SESSION['user_id']."\n");
}

if ($map != false)
{
    $result = mysqli_fetch_assoc($map);
    mysqli_free_result($map);
    $map = $result;
}

$gebaeude = false;

if ($mysql_connection != false)
{
    $gebaeude = mysqli_query($mysql_connection,
                             "SELECT `building`,\n".
                             "    count(1) AS amount\n".
                             "FROM `building`\n".
                             "WHERE `user_id`=".$_SESSION['user_id']."\n".
                             "GROUP BY `building`");
}

if ($gebaeude != false)
{
    $result = array();

    while ($temp = mysqli_fetch_assoc($gebaeude))
    {
        $result[$temp['building']] = $temp['amount'];
    }

    mysqli_free_result($gebaeude);
    $gebaeude = $result;
}

$bauernhoefe = 0;
$holzfaeller = 0;
$steinbrueche = 0;
$handelshaus = isset($gebaeude[ENUM_GEBAEUDE_HANDELSHAUS]);

if (isset($gebaeude[ENUM_GEBAEUDE_BAUERNHOF]) === true)
{
    $bauernhoefe = (int)$gebaeude[ENUM_GEBAEUDE_BAUERNHOF];
}

if (isset($gebaeude[ENUM_GEBAEUDE_HOLZFAELLER]) === true)
{
    $holzfaeller = (int)$gebaeude[ENUM_GEBAEUDE_HOLZFAELLER];
}

if (isset($gebaeude[ENUM_GEBAEUDE_STEINBRUCH]) === true)
{
    $steinbrueche = (int)$gebaeude[ENUM_GEBAEUDE_STEINBRUCH];
}

$messages = false;

if ($mysql_connection != false)
{
    $messages = mysqli_query($mysql_connection,
                             "SELECT `text`".
                             "FROM `message`\n".
                             "WHERE `user_id`=".$_SESSION['user_id']."\n".
                             "ORDER BY `time` ASC");
}

if ($messages != false)
{
    $result = array();

    while ($temp = mysqli_fetch_assoc($messages))
    {
        $result[] = $temp;
    }

    mysqli_free_result($messages);
    $messages = $result;
}



echo "        <table border=\"1\">\n";

if (is_array($ressourcen) === true)
{
    echo "          <tr>\n".
         "            <td colspan=\"2\">\n".
         "              Nahrung: ".$ressourcen['food'].",\n".
         "              Holz: ".$ressourcen['wood'].",\n".
         "              Stein: ".$ressourcen['stone'].",\n".
         "              Kohle: ".$ressourcen['coal'].",\n".
         "              Eisen: ".$ressourcen['iron'].",\n".
         "              Gold: ".$ressourcen['gold'].".\n".
         "            </td>\n".
         "          </tr>\n";
}

echo "          <tr>\n".
     "            <td>\n";

if (is_array($map) === true)
{
    echo "              Land: ".$map['fields_grass']."<br />\n".
         "              Wald: ".$map['fields_wood']."<br />\n".
         "              Gebirge: ".$map['fields_stone']."<br />\n".
         "              Kohlevorkommen: ".$map['fields_coal']."<br />\n".
         "              Eisenvorkommen: ".$map['fields_iron']."<br />\n".
         "              Goldvorkommen: ".$map['fields_gold']."<br />\n".
         "              <br />\n";
}

echo "              Bauernhöfe: ".$bauernhoefe."<br />\n".
     "              Holzfäller: ".$holzfaeller."<br />\n".
     "              Steinbrüche: ".$steinbrueche."<br />\n".
     /*
     "              Kohleminen: <br />\n".
     "              Eisenminen: <br />\n".
     "              Goldminen: <br />\n".
     */
     "              <br />\n";

if ($handelshaus === true)
{
    echo "              Handelshaus.<br />\n";
}

/*
if ($garnison === true)
{
    echo "              Garnison.<br />\n";
}

echo "              <br />\n".
     "              Befestigung: <br />\n".
     "              Soldaten: <br />\n".
*/
echo "            </td>\n".
     "            <td valign=\"top\">\n".
     "              <a href=\"bauen.php\">Bauen</a><br />\n";

if ($handelshaus === true)
{
    echo "              <a href=\"senden.php\">Senden</a><br />\n".
         "              <a href=\"handeln.php\">Handeln</a><br />\n";
}

echo "            </td>\n".
     "          </tr>\n".
     "          <tr>\n".
     "            <td colspan=\"2\">\n";

if (is_array($messages) === true)
{
    foreach ($messages as $message)
    {
        echo "              ".$message['text']."<br />\n";
    }

    require_once("game.inc.php");

    // Nachrichten gelten als abgeholt.
    removeAllMessages($_SESSION['user_id']);
}

echo "            </td>\n".
     "          </tr>\n".
     "        </table>\n".
     "    </body>\n".
     "</html>\n".
     "\n";



?>
