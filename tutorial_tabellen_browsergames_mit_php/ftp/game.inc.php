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
 * @file $/game.inc.php
 * @author Stephan Kreutzer
 * @since 2011-12-07
 */



require_once("defines.inc.php");



// Parameter $name muss zur direkten Verwendung in SQL-Anweisung
// vorbereitet worden sein!
function insertNewUser($name, $passwort)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -2;
    }

    $salz = md5(uniqid(rand(), true));
    // Passwörter dürfen _NIE_ (!!!) im Klartext gespeichert
    // werden!
    $passwort = hash('sha512', $salz.$passwort);

    if (mysql_query("INSERT INTO `user` (`id`,\n".
                    "    `name`,\n".
                    "    `salt`,\n".
                    "    `password`)\n".
                    "VALUES (NULL,\n".
                    "    '".$name."',\n".
                    "    '".$salz."',\n".
                    "    '".$passwort."')\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -3;
    }

    $id = mysql_insert_id($mysql_connection);

    if ($id == 0)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -4;
    }


    // Initialisierung der Spieler-Daten.

    $verbleibende_felder = 24;

    $felder_gras = rand(1, $verbleibende_felder - 2);
    $verbleibende_felder -= $felder_gras;
    $felder_holz = rand(1, $verbleibende_felder - 1);
    $verbleibende_felder -= $felder_holz;
    $felder_stein = rand(1, $verbleibende_felder);
    $verbleibende_felder -= $felder_stein;
    $felder_kohle = rand(0, $verbleibende_felder);
    $verbleibende_felder -= $felder_kohle;
    $felder_eisen = rand(0, $verbleibende_felder);
    $verbleibende_felder -= $felder_eisen;
    $felder_gold = $verbleibende_felder;

    if (mysql_query("INSERT INTO `user_map` (`user_id`,\n".
                    "    `fields_grass`,\n".
                    "    `fields_wood`,\n".
                    "    `fields_stone`,\n".
                    "    `fields_coal`,\n".
                    "    `fields_iron`,\n".
                    "    `fields_gold`)\n".
                    "VALUES (".$id.",\n".
                    "    ".$felder_gras.",\n".
                    "    ".$felder_holz.",\n".
                    "    ".$felder_stein.",\n".
                    "    ".$felder_kohle.",\n".
                    "    ".$felder_eisen.",\n".
                    "    ".$felder_gold.")\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -5;
    }

    if (mysql_query("INSERT INTO `user_resource` (`user_id`,\n".
                    "    `food`,\n".
                    "    `wood`,\n".
                    "    `stone`,\n".
                    "    `coal`,\n".
                    "    `iron`,\n".
                    "    `gold`)\n".
                    "VALUES (".$id.",\n".
                    "    10,\n".
                    "    6,\n".
                    "    0,\n".
                    "    0,\n".
                    "    0,\n".
                    "    0)\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -6;
    }

    if (mysql_query("COMMIT", $mysql_connection) === true)
    {
        return $id;
    }

    mysql_query("ROLLBACK", $mysql_connection);
    return 0;
}

function updateUser($userID)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }


    $messages = array();

    // Fertige Gebaeude von der Baureihe in die Gebaeude-Liste
    // aufnehmen.

    $bauschlange = mysql_query("SELECT `building`,\n".
                               "    `ready`\n".
                               "FROM `build_queue`\n".
                               "WHERE `user_id`=".$userID." AND\n".
                               "    `ready`<CURDATE()\n".
                               "ORDER BY `ready` ASC",
                               $mysql_connection);

    if ($bauschlange != false)
    {
        $result = array();

        while ($temp = mysql_fetch_assoc($bauschlange))
        {
            $result[] = $temp;
        }

        mysql_free_result($bauschlange);
        $bauschlange = $result;
    }
    else
    {
        return -2;
    }


    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -3;
    }

    foreach ($bauschlange as $gebaeude)
    {
        if (mysql_query("INSERT INTO `building` (`user_id`,\n".
                        "    `building`,\n".
                        "    `timer`)\n".
                        "VALUES(".$userID.",\n".
                        "    '".$gebaeude['building']."',\n".
                        "    '".$gebaeude['ready']."')\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -4;
        }

        $messages[] = translateEnumGebaeudeToDisplayText($gebaeude['building'])." am ".$gebaeude['ready']." fertiggestellt.";
    }

    if (mysql_query("DELETE\n".
                    "FROM `build_queue`\n".
                    "WHERE `user_id`=".$userID." AND\n".
                    "    `ready`<CURDATE()",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -5;
    }


    // Ressourcen aus den Gebaeuden schoepfen.

    $ressourcen = mysql_query("SELECT `food`,".
                              "    `wood`,\n".
                              "    `stone`,\n".
                              "    `coal`,\n".
                              "    `iron`,\n".
                              "    `gold`\n".
                              "FROM `user_resource`\n".
                              "WHERE `user_id`=".$userID."\n",
                              $mysql_connection);

    if ($ressourcen != false)
    {
        $result = mysql_fetch_assoc($ressourcen);
        mysql_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -6;
    }

    // TODO: Hier evtl. Stadtgebaeude ausschliessen...
    $gebaeude = mysql_query("SELECT `building`,\n".
                            "    `timer`\n".
                            "FROM `building`\n".
                            "WHERE `user_id`=".$userID." AND\n".
                            "    `timer`<CURDATE()",
                            $mysql_connection);

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
    else
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -7;
    }

    $bauernhoefe = 0;
    $holzfaeller = 0;
    $steinbrueche = 0;

    foreach ($gebaeude as $ein_gebaeude)
    {
        $tagedifferenz = strtotime(date("Y-m-d")) - strtotime($ein_gebaeude['timer']);
        $tagedifferenz = floor($tagedifferenz / 86400);

        if ($tagedifferenz < 0)
        {
            // Sicher ist sicher...
            continue;
        }

        switch ($ein_gebaeude['building'])
        {
        case ENUM_GEBAEUDE_BAUERNHOF:
            $bauernhoefe += $tagedifferenz;
            break;
        case ENUM_GEBAEUDE_HOLZFAELLER:
            $holzfaeller += $tagedifferenz;
            break;
        case ENUM_GEBAEUDE_STEINBRUCH:
            $steinbrueche += $tagedifferenz;
            break;
        }
    }

    $nahrung = $ressourcen['food'];
    $holz = $ressourcen['wood'];
    $stein = $ressourcen['stone'];

    $nahrung += 2 * $bauernhoefe;

    for ($i = 0; $i < $holzfaeller; $i++)
    {
        if ($nahrung > 0)
        {
            $nahrung -= 1;
            $holz += 1;
        }
        else
        {
            break;
        }
    }

    for ($i = 0; $i < $steinbrueche; $i++)
    {
        if ($nahrung > 0)
        {
            $nahrung -= 1;
            $stein += 1;
        }
        else
        {
            break;
        }
    }

    if (mysql_query("UPDATE `user_resource`\n".
                    "SET `food`=".$nahrung.",\n".
                    "    `wood`=".$holz.",\n".
                    "    `stone`=".$stein."\n".
                    "WHERE `user_id`=".$userID."\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -8;
    }

    $messages[] = "Ressourcenschöpfung: ".
                  sprintf("%+d", $nahrung - $ressourcen['food'])." Nahrung, ".
                  sprintf("%+d", $holz - $ressourcen['wood'])." Holz und ".
                  sprintf("%+d", $stein - $ressourcen['stone'])." Stein.";

    // Vermerken, dass Ressourcen geholt wurden.
    if (mysql_query("UPDATE `building`\n".
                    "SET `timer`=CURDATE()\n".
                    "WHERE `user_id`=".$userID." AND\n".
                    "    `timer`<CURDATE()",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -9;
    }

    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -10;
    }

    addMessages($userID, $messages);

    return 0;
}

// Return-Werte:
// -6 Zu wenig Holz.
// -7 Zu wenig Stein.
function insertNewBuilding($userID, $gebaeude)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }


    $map = mysql_query("SELECT `fields_grass`,".
                       "    `fields_wood`,\n".
                       "    `fields_stone`,\n".
                       "    `fields_coal`,\n".
                       "    `fields_iron`,\n".
                       "    `fields_gold`\n".
                       "FROM `user_map`\n".
                       "WHERE `user_id`=".$_SESSION['user_id']."\n",
                       $mysql_connection);

    if ($map != false)
    {
        $result = mysql_fetch_assoc($map);
        mysql_free_result($map);
        $map = $result;
    }
    else
    {
        return -2;
    }

    $ressourcen = mysql_query("SELECT `food`,".
                              "    `wood`,\n".
                              "    `stone`,\n".
                              "    `coal`,\n".
                              "    `iron`,\n".
                              "    `gold`\n".
                              "FROM `user_resource`\n".
                              "WHERE `user_id`=".$userID."\n",
                              $mysql_connection);

    if ($ressourcen != false)
    {
        $result = mysql_fetch_assoc($ressourcen);
        mysql_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -3;
    }


    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -4;
    }

    switch ($gebaeude)
    {
    case ENUM_GEBAEUDE_BAUERNHOF:
        if ($map['fields_grass'] <= 0)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -5;
        }

        if ($ressourcen['wood'] < 3)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysql_query("INSERT INTO `build_queue` (`user_id`,\n".
                        "    `building`,\n".
                        "    `ready`)\n".
                        "VALUES (".$userID.",\n".
                        "    '".$gebaeude."',\n".
                        "    CURDATE() + 3)\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -100;
        }

        if (mysql_query("UPDATE `user_map`\n".
                        "SET `fields_grass`=`fields_grass` - 1\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -101;
        }

        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `wood`=`wood` - 3\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_HOLZFAELLER:
        if ($map['fields_wood'] <= 0)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -5;
        }

        if ($ressourcen['wood'] < 1)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysql_query("INSERT INTO `build_queue` (`user_id`,\n".
                        "    `building`,\n".
                        "    `ready`)\n".
                        "VALUES (".$userID.",\n".
                        "    '".$gebaeude."',\n".
                        "    CURDATE() + 1)\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -100;
        }

        if (mysql_query("UPDATE `user_map`\n".
                        "SET `fields_wood`=`fields_wood` - 1\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -101;
        }

        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `wood`=`wood` - 1\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_STEINBRUCH:
        if ($map['fields_stone'] <= 0)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -5;
        }

        if ($ressourcen['wood'] < 2)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysql_query("INSERT INTO `build_queue` (`user_id`,\n".
                        "    `building`,\n".
                        "    `ready`)\n".
                        "VALUES (".$userID.",\n".
                        "    '".$gebaeude."',\n".
                        "    CURDATE() + 2)\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -100;
        }

        if (mysql_query("UPDATE `user_map`\n".
                        "SET `fields_stone`=`fields_stone` - 1\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -101;
        }

        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `wood`=`wood` - 2\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_HANDELSHAUS:
        if ($ressourcen['wood'] < 5)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            // Feststehender Signal-Wert.
            return -6;
        }

        if ($ressourcen['stone'] < 5)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            // Feststehender Signal-Wert.
            return -7;
        }

        {
            // Vielleicht eher COUNT?
            $bestehende_gebaeude = mysql_query("SELECT `building`\n".
                                               "FROM `building`\n".
                                               "WHERE `user_id`=".$userID." AND\n".
                                               "    `building`='".$gebaeude."'",
                                               $mysql_connection);

            if ($bestehende_gebaeude == false)
            {
                mysql_query("ROLLBACK", $mysql_connection);
                return -8;
            }

            if (mysql_num_rows($bestehende_gebaeude) > 0)
            {
                mysql_query("ROLLBACK", $mysql_connection);
                return -9;
            }
        }

        {
            // Vielleicht eher COUNT?
            $beauftragte_gebaeude = mysql_query("SELECT `ready`\n".
                                                "FROM `build_queue`\n".
                                                "WHERE `user_id`=".$userID." AND\n".
                                                "    `building`='".$gebaeude."'",
                                                $mysql_connection);

            if ($beauftragte_gebaeude == false)
            {
                mysql_query("ROLLBACK", $mysql_connection);
                return -8;
            }

            if (mysql_num_rows($beauftragte_gebaeude) > 0)
            {
                mysql_query("ROLLBACK", $mysql_connection);
                return -9;
            }
        }

        if (mysql_query("INSERT INTO `build_queue` (`user_id`,\n".
                        "    `building`,\n".
                        "    `ready`)\n".
                        "VALUES (".$userID.",\n".
                        "    '".$gebaeude."',\n".
                        "    CURDATE() + 4)\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -100;
        }

        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `wood`=`wood` - 5,\n".
                        "    `stone`=`stone` - 5\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -102;
        }

        break;
    default:
        mysql_query("ROLLBACK", $mysql_connection);
        return -103;
    }

    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -104;
    }

    return 0;
}

// Return-Werte:
// -4 Empfaenger existiert nicht.
// -6 Menge zu hoch.
function sendResources($userID, $menge, $ressourceArt, $empfaenger, $sender = NULL)
{
    if ($menge == 0)
    {
        return 0;
    }

    if ($menge < 0)
    {
        $menge *= -1;
    }


    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    $messages = array();

    // Benutzer-Eingaben müssen _IMMER_ (!!!) erst bearbeitet
    // werden, bevor sie in ein SQL-Query eingefügt werden dürfen.
    // Es könnten unerlaubte Zeichen enthalten sein mit dem Ziel,
    // die Datenbank zu manipulieren.
    $menge = mysql_real_escape_string($menge);
    $ressourceArt = mysql_real_escape_string($ressourceArt);
    $empfaenger = mysql_real_escape_string($empfaenger);

    if ($menge == false ||
        $ressourceArt == false ||
        $empfaenger == false)
    {
        return -2;
    }

    $empfaengerID = mysql_query("SELECT `id`\n".
                                "FROM `user`\n".
                                "WHERE `name` LIKE '".$empfaenger."'",
                                $mysql_connection);

    if ($empfaengerID == false)
    {
        return -3;
    }

    if (mysql_num_rows($empfaengerID) == 1)
    {
        $result = mysql_fetch_assoc($empfaengerID);
        mysql_free_result($empfaengerID);
        $empfaengerID = $result['id'];
    }
    else
    {
        // Feststehender Signal-Wert.
        return -4;
    }

    $ressourcen = mysql_query("SELECT `".$ressourceArt."`".
                              "FROM `user_resource`\n".
                              "WHERE `user_id`=".$userID."\n",
                              $mysql_connection);

    if ($ressourcen != false)
    {
        $result = mysql_fetch_assoc($ressourcen);
        mysql_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -5;
    }

    if ($ressourcen[$ressourceArt] < $menge)
    {
        // Feststehender Signal-Wert.
        return -6;
    }

    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -7;
    }

    if (mysql_query("UPDATE `user_resource`\n".
                    "SET `".$ressourceArt."`=`".$ressourceArt."` - ".$menge."\n".
                    "WHERE `user_id`=".$userID."\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -8;
    }

    if (mysql_query("UPDATE `user_resource`\n".
                    "SET `".$ressourceArt."`=`".$ressourceArt."` + ".$menge."\n".
                    "WHERE `user_id`=".$empfaengerID."\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -9;
    }

    if (is_string($sender) === true)
    {
        $messages[] = $menge." ".translateEnumResourceToDisplayText($ressourceArt)." von ".$sender." erhalten.";
    }
    else
    {
        $messages[] = $menge." ".translateEnumResourceToDisplayText($ressourceArt)." erhalten.";
    }

    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -10;
    }

    addMessages($empfaengerID, $messages);

    return 0;
}

// Return-Werte:
// -2 Ressourcen-Typen sind gleich.
// -6 Gegen-Menge zu hoch.
function placeResourceTrade($userID, $sucheMenge, $sucheRessourceArt, $gegenMenge, $gegenRessourceArt)
{
    if ($sucheMenge == 0 &&
        $gegenMenge == 0)
    {
        return -1;
    }

    if ($sucheRessourceArt == $gegenRessourceArt)
    {
        // Feststehender Signal-Wert.
        return -2;
    }

    if ($sucheMenge < 0)
    {
        $sucheMenge *= -1;
    }

    if ($gegenMenge < 0)
    {
        $gegenMenge *= -1;
    }


    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -3;
    }

    // Benutzer-Eingaben müssen _IMMER_ (!!!) erst bearbeitet
    // werden, bevor sie in ein SQL-Query eingefügt werden dürfen.
    // Es könnten unerlaubte Zeichen enthalten sein mit dem Ziel,
    // die Datenbank zu manipulieren.
    $sucheMenge = mysql_real_escape_string($sucheMenge);
    $sucheRessourceArt = mysql_real_escape_string($sucheRessourceArt);
    $gegenMenge = mysql_real_escape_string($gegenMenge);
    $gegenRessourceArt = mysql_real_escape_string($gegenRessourceArt);

    if ($sucheMenge == false ||
        $sucheRessourceArt == false ||
        $gegenMenge == false ||
        $gegenRessourceArt == false)
    {
        return -4;
    }

    $ressourcen = mysql_query("SELECT `".$gegenRessourceArt."`".
                              "FROM `user_resource`\n".
                              "WHERE `user_id`=".$userID."\n",
                              $mysql_connection);

    if ($ressourcen != false)
    {
        $result = mysql_fetch_assoc($ressourcen);
        mysql_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -5;
    }

    if ($ressourcen[$gegenRessourceArt] < $gegenMenge)
    {
        // Feststehender Signal-Wert.
        return -6;
    }

    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -7;
    }

    if (mysql_query("UPDATE `user_resource`\n".
                    "SET `".$gegenRessourceArt."`=`".$gegenRessourceArt."` - ".$gegenMenge."\n".
                    "WHERE `user_id`=".$userID."\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -8;
    }

    if (mysql_query("INSERT INTO `trading` (`id`,\n".
                    "    `give_amount`,\n".
                    "    `give_type`,\n".
                    "    `get_amount`,\n".
                    "    `get_type`,\n".
                    "    `time`,\n".
                    "    `user_id`)\n".
                    "VALUES (NULL,\n".
                    "    ".$gegenMenge.",\n".
                    "    '".$gegenRessourceArt."',\n".
                    "    ".$sucheMenge.",\n".
                    "    '".$sucheRessourceArt."',\n".
                    "    NULL,\n".
                    "    ".$userID.")\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -9;
    }


    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -10;
    }

    return 0;
}

// Return-Werte:
// -9 Gegen-Kosten zu hoch (= eigene Ressourcen zu niedrig).
// 1 Trade geloescht.
// 2 Trade angenommen.
function handleResourceTrade($tradeID, $userID)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    // Benutzer-Eingaben müssen _IMMER_ (!!!) erst bearbeitet
    // werden, bevor sie in ein SQL-Query eingefügt werden dürfen.
    // Es könnten unerlaubte Zeichen enthalten sein mit dem Ziel,
    // die Datenbank zu manipulieren.
    $tradeID = mysql_real_escape_string($tradeID);

    if ($tradeID == false)
    {
        return -2;
    }

    $trade = mysql_query("SELECT `give_amount`,\n".
                         "    `give_type`,\n".
                         "    `get_amount`,\n".
                         "    `get_type`,\n".
                         "    `user_id`\n".
                         "FROM `trading`\n".
                         "WHERE `id`=".$tradeID."\n",
                         $mysql_connection);

    if ($trade != false)
    {
        $result = mysql_fetch_assoc($trade);
        mysql_free_result($trade);
        $trade = $result;
    }
    else
    {
        return -3;
    }

    if ($trade['user_id'] == $userID)
    {
        // Der zu behandelnde Trade gehoert dem User,
        // also will dieser eine Loesch-Aktion durchfuehren.

        if (mysql_query("BEGIN", $mysql_connection) !== true)
        {
            return -4;
        }

        // Ressourcen aus dem Handelshaus an den User
        // zurueckgeben.
        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `".$trade['give_type']."`=`".$trade['give_type']."` + ".$trade['give_amount']."\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -5;
        }

        if (mysql_query("DELETE\n".
                        "FROM `trading`\n".
                        "WHERE `id`=".$tradeID,
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -6;
        }

        if (mysql_query("COMMIT", $mysql_connection) !== true)
        {
            return -7;
        }

        // Feststehender Signal-Wert.
        return 1;
    }
    else
    {
        // Der User geht auf ein fremdes Angebot ein.

        $messages = array();

        $ressourcen = mysql_query("SELECT `".$trade['get_type']."`".
                                  "FROM `user_resource`\n".
                                  "WHERE `user_id`=".$userID."\n",
                                  $mysql_connection);

        if ($ressourcen != false)
        {
            $result = mysql_fetch_assoc($ressourcen);
            mysql_free_result($ressourcen);
            $ressourcen = $result;
        }
        else
        {
            return -8;
        }

        if ($ressourcen[$trade['get_type']] < $trade['get_amount'])
        {
            // Feststehender Signal-Wert.
            return -9;
        }

        if (mysql_query("BEGIN", $mysql_connection) !== true)
        {
            return -10;
        }

        // User.
        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `".$trade['give_type']."`=`".$trade['give_type']."` + ".$trade['give_amount'].",\n".
                        "    `".$trade['get_type']."`=`".$trade['get_type']."` - ".$trade['get_amount']."\n".
                        "WHERE `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -11;
        }

        // User des Angebots ("give" wurde bereits beim Eintragen des
        // Trades abgezogen, sodass anschliessend nur noch der Trade
        // geloescht werden muss).
        if (mysql_query("UPDATE `user_resource`\n".
                        "SET `".$trade['get_type']."`=`".$trade['get_type']."` + ".$trade['get_amount']."\n".
                        "WHERE `user_id`=".$trade['user_id']."\n",
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -12;
        }

        $messages[] = "Jemand hat deine ".$trade['give_amount']." ".translateEnumResourceToDisplayText($trade['give_type'])." gegen ".$trade['get_amount']." ".translateEnumResourceToDisplayText($trade['get_type'])." gehandelt.";

        if (mysql_query("DELETE\n".
                        "FROM `trading`\n".
                        "WHERE `id`=".$tradeID,
                        $mysql_connection) !== true)
        {
            mysql_query("ROLLBACK", $mysql_connection);
            return -13;
        }

        if (mysql_query("COMMIT", $mysql_connection) !== true)
        {
            return -14;
        }

        addMessages($trade['user_id'], $messages);

        // Feststehender Signal-Wert.
        return 2;
    }

    return -15;
}

function addMessages($userID, $messages)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    foreach ($messages as $message)
    {
        // Potentiell auf Benutzer-Eingaben basierende Werte müssen
        // _IMMER_ (!!!) erst bearbeitet werden, bevor sie in ein
        // SQL-Query eingefügt werden dürfen. Es könnten unerlaubte
        // Zeichen enthalten sein mit dem Ziel, die Datenbank zu
        // manipulieren.
        $message = mysql_real_escape_string($message);

        if ($message == false)
        {
            continue;
        }

        @mysql_query("INSERT INTO `message` (`id`,\n".
                     "    `text`,\n".
                     "    `time`,\n".
                     "    `user_id`)\n".
                     "VALUES (NULL,\n".
                     "    '".$message."',\n".
                     "    NULL,\n".
                     "    ".$userID.")\n",
                     $mysql_connection);
    }

    return 0;
}

function removeAllMessages($userID)
{
    require_once("database.inc.php");
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (mysql_query("DELETE\n".
                    "FROM `message`\n".
                    "WHERE `user_id`=".$userID,
                    $mysql_connection) !== true)
    {
        return -2;
    }

    return 0;
}

?>
