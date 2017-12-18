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

    if (mysqli_query($mysql_connection, "BEGIN") !== true)
    {
        return -2;
    }

    $salz = md5(uniqid(rand(), true));
    // Passwörter dürfen _NIE_ (!!!) im Klartext gespeichert
    // werden!
    $passwort = hash('sha512', $salz.$passwort);

    if (mysqli_query($mysql_connection,
                     "INSERT INTO `user` (`id`,\n".
                     "    `name`,\n".
                     "    `salt`,\n".
                     "    `password`)\n".
                     "VALUES (NULL,\n".
                     "    '".$name."',\n".
                     "    '".$salz."',\n".
                     "    '".$passwort."')\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -3;
    }

    $id = mysqli_insert_id($mysql_connection);

    if ($id == 0)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
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

    if (mysqli_query($mysql_connection,
                     "INSERT INTO `user_map` (`user_id`,\n".
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
                     "    ".$felder_gold.")\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -5;
    }

    if (mysqli_query($mysql_connection,
                     "INSERT INTO `user_resource` (`user_id`,\n".
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
                     "    0)\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -6;
    }

    if (mysqli_query($mysql_connection, "COMMIT") === true)
    {
        return $id;
    }

    mysqli_query($mysql_connection, "ROLLBACK");
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

    $bauschlange = mysqli_query($mysql_connection,
                                "SELECT `building`,\n".
                                "    `ready`\n".
                                "FROM `build_queue`\n".
                                "WHERE `user_id`=".$userID." AND\n".
                                "    `ready`<CURDATE()\n".
                                "ORDER BY `ready` ASC");

    if ($bauschlange != false)
    {
        $result = array();

        while ($temp = mysqli_fetch_assoc($bauschlange))
        {
            $result[] = $temp;
        }

        mysqli_free_result($bauschlange);
        $bauschlange = $result;
    }
    else
    {
        return -2;
    }


    if (mysqli_query($mysql_connection, "BEGIN") !== true)
    {
        return -3;
    }

    foreach ($bauschlange as $gebaeude)
    {
        if (mysqli_query($mysql_connection,
                         "INSERT INTO `building` (`user_id`,\n".
                         "    `building`,\n".
                         "    `timer`)\n".
                         "VALUES(".$userID.",\n".
                         "    '".$gebaeude['building']."',\n".
                         "    '".$gebaeude['ready']."')\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -4;
        }

        $messages[] = translateEnumGebaeudeToDisplayText($gebaeude['building'])." am ".$gebaeude['ready']." fertiggestellt.";
    }

    if (mysqli_query($mysql_connection,
                     "DELETE\n".
                     "FROM `build_queue`\n".
                     "WHERE `user_id`=".$userID." AND\n".
                     "    `ready`<CURDATE()") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -5;
    }


    // Ressourcen aus den Gebaeuden schoepfen.

    $ressourcen = mysqli_query($mysql_connection,
                               "SELECT `food`,".
                               "    `wood`,\n".
                               "    `stone`,\n".
                               "    `coal`,\n".
                               "    `iron`,\n".
                               "    `gold`\n".
                               "FROM `user_resource`\n".
                               "WHERE `user_id`=".$userID."\n");

    if ($ressourcen != false)
    {
        $result = mysqli_fetch_assoc($ressourcen);
        mysqli_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -6;
    }

    // TODO: Hier evtl. Stadtgebaeude ausschliessen...
    $gebaeude = mysqli_query($mysql_connection,
                             "SELECT `building`,\n".
                             "    `timer`\n".
                             "FROM `building`\n".
                             "WHERE `user_id`=".$userID." AND\n".
                             "    `timer`<CURDATE()");

    if ($gebaeude != false)
    {
        $result = array();

        while ($temp = mysqli_fetch_assoc($gebaeude))
        {
            $result[] = $temp;
        }

        mysqli_free_result($gebaeude);
        $gebaeude = $result;
    }
    else
    {
        mysqli_query($mysql_connection, "ROLLBACK");
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

    if (mysqli_query($mysql_connection,
                     "UPDATE `user_resource`\n".
                     "SET `food`=".$nahrung.",\n".
                     "    `wood`=".$holz.",\n".
                     "    `stone`=".$stein."\n".
                     "WHERE `user_id`=".$userID."\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -8;
    }

    $messages[] = "Ressourcenschöpfung: ".
                  sprintf("%+d", $nahrung - $ressourcen['food'])." Nahrung, ".
                  sprintf("%+d", $holz - $ressourcen['wood'])." Holz und ".
                  sprintf("%+d", $stein - $ressourcen['stone'])." Stein.";

    // Vermerken, dass Ressourcen geholt wurden.
    if (mysqli_query($mysql_connection,
                     "UPDATE `building`\n".
                     "SET `timer`=CURDATE()\n".
                     "WHERE `user_id`=".$userID." AND\n".
                     "    `timer`<CURDATE()") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -9;
    }

    if (mysqli_query($mysql_connection, "COMMIT") !== true)
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


    $map = mysqli_query($mysql_connection,
                        "SELECT `fields_grass`,".
                        "    `fields_wood`,\n".
                        "    `fields_stone`,\n".
                        "    `fields_coal`,\n".
                        "    `fields_iron`,\n".
                        "    `fields_gold`\n".
                        "FROM `user_map`\n".
                        "WHERE `user_id`=".$_SESSION['user_id']."\n");

    if ($map != false)
    {
        $result = mysqli_fetch_assoc($map);
        mysqli_free_result($map);
        $map = $result;
    }
    else
    {
        return -2;
    }

    $ressourcen = mysqli_query($mysql_connection,
                               "SELECT `food`,".
                               "    `wood`,\n".
                               "    `stone`,\n".
                               "    `coal`,\n".
                               "    `iron`,\n".
                               "    `gold`\n".
                               "FROM `user_resource`\n".
                               "WHERE `user_id`=".$userID."\n");

    if ($ressourcen != false)
    {
        $result = mysqli_fetch_assoc($ressourcen);
        mysqli_free_result($ressourcen);
        $ressourcen = $result;
    }
    else
    {
        return -3;
    }


    if (mysqli_query($mysql_connection, "BEGIN") !== true)
    {
        return -4;
    }

    switch ($gebaeude)
    {
    case ENUM_GEBAEUDE_BAUERNHOF:
        if ($map['fields_grass'] <= 0)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -5;
        }

        if ($ressourcen['wood'] < 3)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysqli_query($mysql_connection,
                         "INSERT INTO `build_queue` (`user_id`,\n".
                         "    `building`,\n".
                         "    `ready`)\n".
                         "VALUES (".$userID.",\n".
                         "    '".$gebaeude."',\n".
                         "    CURDATE() + 3)\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -100;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_map`\n".
                         "SET `fields_grass`=`fields_grass` - 1\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -101;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `wood`=`wood` - 3\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_HOLZFAELLER:
        if ($map['fields_wood'] <= 0)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -5;
        }

        if ($ressourcen['wood'] < 1)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysqli_query($mysql_connection,
                         "INSERT INTO `build_queue` (`user_id`,\n".
                         "    `building`,\n".
                         "    `ready`)\n".
                         "VALUES (".$userID.",\n".
                         "    '".$gebaeude."',\n".
                         "    CURDATE() + 1)\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -100;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_map`\n".
                         "SET `fields_wood`=`fields_wood` - 1\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -101;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `wood`=`wood` - 1\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_STEINBRUCH:
        if ($map['fields_stone'] <= 0)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -5;
        }

        if ($ressourcen['wood'] < 2)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            // Feststehender Signal-Wert.
            return -6;
        }

        if (mysqli_query($mysql_connection,
                         "INSERT INTO `build_queue` (`user_id`,\n".
                         "    `building`,\n".
                         "    `ready`)\n".
                         "VALUES (".$userID.",\n".
                         "    '".$gebaeude."',\n".
                         "    CURDATE() + 2)\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -100;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_map`\n".
                         "SET `fields_stone`=`fields_stone` - 1\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -101;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `wood`=`wood` - 2\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -102;
        }

        break;
    case ENUM_GEBAEUDE_HANDELSHAUS:
        if ($ressourcen['wood'] < 5)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            // Feststehender Signal-Wert.
            return -6;
        }

        if ($ressourcen['stone'] < 5)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            // Feststehender Signal-Wert.
            return -7;
        }

        {
            // Vielleicht eher COUNT?
            $bestehende_gebaeude = mysqli_query($mysql_connection,
                                                "SELECT `building`\n".
                                                "FROM `building`\n".
                                                "WHERE `user_id`=".$userID." AND\n".
                                                "    `building`='".$gebaeude."'");

            if ($bestehende_gebaeude == false)
            {
                mysqli_query($mysql_connection, "ROLLBACK");
                return -8;
            }

            if (mysqli_num_rows($bestehende_gebaeude) > 0)
            {
                mysqli_query($mysql_connection, "ROLLBACK");
                return -9;
            }
        }

        {
            // Vielleicht eher COUNT?
            $beauftragte_gebaeude = mysqli_query($mysql_connection,
                                                 "SELECT `ready`\n".
                                                 "FROM `build_queue`\n".
                                                 "WHERE `user_id`=".$userID." AND\n".
                                                 "    `building`='".$gebaeude."'");

            if ($beauftragte_gebaeude == false)
            {
                mysqli_query($mysql_connection, "ROLLBACK");
                return -8;
            }

            if (mysqli_num_rows($beauftragte_gebaeude) > 0)
            {
                mysqli_query($mysql_connection, "ROLLBACK");
                return -9;
            }
        }

        if (mysqli_query($mysql_connection,
                         "INSERT INTO `build_queue` (`user_id`,\n".
                         "    `building`,\n".
                         "    `ready`)\n".
                         "VALUES (".$userID.",\n".
                         "    '".$gebaeude."',\n".
                         "    CURDATE() + 4)\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -100;
        }

        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `wood`=`wood` - 5,\n".
                         "    `stone`=`stone` - 5\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -102;
        }

        break;
    default:
        mysqli_query($mysql_connection, "ROLLBACK");
        return -103;
    }

    if (mysqli_query($mysql_connection, "COMMIT") !== true)
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
    $menge = mysqli_real_escape_string($mysql_connection, $menge);
    $ressourceArt = mysqli_real_escape_string($mysql_connection, $ressourceArt);
    $empfaenger = mysqli_real_escape_string($mysql_connection, $empfaenger);

    if ($menge == false ||
        $ressourceArt == false ||
        $empfaenger == false)
    {
        return -2;
    }

    $empfaengerID = mysqli_query($mysql_connection,
                                 "SELECT `id`\n".
                                 "FROM `user`\n".
                                 "WHERE `name` LIKE '".$empfaenger."'");

    if ($empfaengerID == false)
    {
        return -3;
    }

    if (mysqli_num_rows($empfaengerID) == 1)
    {
        $result = mysqli_fetch_assoc($empfaengerID);
        mysqli_free_result($empfaengerID);
        $empfaengerID = $result['id'];
    }
    else
    {
        // Feststehender Signal-Wert.
        return -4;
    }

    $ressourcen = mysqli_query($mysql_connection,
                               "SELECT `".$ressourceArt."`".
                               "FROM `user_resource`\n".
                               "WHERE `user_id`=".$userID."\n");

    if ($ressourcen != false)
    {
        $result = mysqli_fetch_assoc($ressourcen);
        mysqli_free_result($ressourcen);
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

    if (mysqli_query($mysql_connection, "BEGIN") !== true)
    {
        return -7;
    }

    if (mysqli_query($mysql_connection,
                     "UPDATE `user_resource`\n".
                     "SET `".$ressourceArt."`=`".$ressourceArt."` - ".$menge."\n".
                     "WHERE `user_id`=".$userID."\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -8;
    }

    if (mysqli_query($mysql_connection,
                     "UPDATE `user_resource`\n".
                     "SET `".$ressourceArt."`=`".$ressourceArt."` + ".$menge."\n".
                     "WHERE `user_id`=".$empfaengerID."\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
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

    if (mysqli_query($mysql_connection, "COMMIT") !== true)
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
    $sucheMenge = mysqli_real_escape_string($mysql_connection, $sucheMenge);
    $sucheRessourceArt = mysqli_real_escape_string($mysql_connection, $sucheRessourceArt);
    $gegenMenge = mysqli_real_escape_string($mysql_connection, $gegenMenge);
    $gegenRessourceArt = mysqli_real_escape_string($mysql_connection, $gegenRessourceArt);

    if ($sucheMenge == false ||
        $sucheRessourceArt == false ||
        $gegenMenge == false ||
        $gegenRessourceArt == false)
    {
        return -4;
    }

    $ressourcen = mysqli_query($mysql_connection,
                               "SELECT `".$gegenRessourceArt."`".
                               "FROM `user_resource`\n".
                               "WHERE `user_id`=".$userID."\n");

    if ($ressourcen != false)
    {
        $result = mysqli_fetch_assoc($ressourcen);
        mysqli_free_result($ressourcen);
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

    if (mysqli_query($mysql_connection, "BEGIN") !== true)
    {
        return -7;
    }

    if (mysqli_query($mysql_connection,
                     "UPDATE `user_resource`\n".
                     "SET `".$gegenRessourceArt."`=`".$gegenRessourceArt."` - ".$gegenMenge."\n".
                     "WHERE `user_id`=".$userID."\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -8;
    }

    if (mysqli_query($mysql_connection,
                     "INSERT INTO `trading` (`id`,\n".
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
                     "    ".$userID.")\n") !== true)
    {
        mysqli_query($mysql_connection, "ROLLBACK");
        return -9;
    }


    if (mysqli_query($mysql_connection, "COMMIT") !== true)
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
    $tradeID = mysqli_real_escape_string($mysql_connection, $tradeID);

    if ($tradeID == false)
    {
        return -2;
    }

    $trade = mysqli_query($mysql_connection,
                          "SELECT `give_amount`,\n".
                          "    `give_type`,\n".
                          "    `get_amount`,\n".
                          "    `get_type`,\n".
                          "    `user_id`\n".
                          "FROM `trading`\n".
                          "WHERE `id`=".$tradeID."\n");

    if ($trade != false)
    {
        $result = mysqli_fetch_assoc($trade);
        mysqli_free_result($trade);
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

        if (mysqli_query($mysql_connection, "BEGIN") !== true)
        {
            return -4;
        }

        // Ressourcen aus dem Handelshaus an den User
        // zurueckgeben.
        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `".$trade['give_type']."`=`".$trade['give_type']."` + ".$trade['give_amount']."\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -5;
        }

        if (mysqli_query($mysql_connection,
                         "DELETE\n".
                         "FROM `trading`\n".
                         "WHERE `id`=".$tradeID) !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -6;
        }

        if (mysqli_query($mysql_connection, "COMMIT") !== true)
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

        $ressourcen = mysqli_query($mysql_connection,
                                   "SELECT `".$trade['get_type']."`".
                                   "FROM `user_resource`\n".
                                   "WHERE `user_id`=".$userID."\n");

        if ($ressourcen != false)
        {
            $result = mysqli_fetch_assoc($ressourcen);
            mysqli_free_result($ressourcen);
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

        if (mysqli_query($mysql_connection, "BEGIN") !== true)
        {
            return -10;
        }

        // User.
        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `".$trade['give_type']."`=`".$trade['give_type']."` + ".$trade['give_amount'].",\n".
                         "    `".$trade['get_type']."`=`".$trade['get_type']."` - ".$trade['get_amount']."\n".
                         "WHERE `user_id`=".$userID."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -11;
        }

        // User des Angebots ("give" wurde bereits beim Eintragen des
        // Trades abgezogen, sodass anschliessend nur noch der Trade
        // geloescht werden muss).
        if (mysqli_query($mysql_connection,
                         "UPDATE `user_resource`\n".
                         "SET `".$trade['get_type']."`=`".$trade['get_type']."` + ".$trade['get_amount']."\n".
                         "WHERE `user_id`=".$trade['user_id']."\n") !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -12;
        }

        $messages[] = "Jemand hat deine ".$trade['give_amount']." ".translateEnumResourceToDisplayText($trade['give_type'])." gegen ".$trade['get_amount']." ".translateEnumResourceToDisplayText($trade['get_type'])." gehandelt.";

        if (mysqli_query($mysql_connection,
                         "DELETE\n".
                         "FROM `trading`\n".
                         "WHERE `id`=".$tradeID) !== true)
        {
            mysqli_query($mysql_connection, "ROLLBACK");
            return -13;
        }

        if (mysqli_query($mysql_connection, "COMMIT") !== true)
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
        $message = mysqli_real_escape_string($mysql_connection, $message);

        if ($message == false)
        {
            continue;
        }

        @mysqli_query($mysql_connection,
                      "INSERT INTO `message` (`id`,\n".
                      "    `text`,\n".
                      "    `time`,\n".
                      "    `user_id`)\n".
                      "VALUES (NULL,\n".
                      "    '".$message."',\n".
                      "    NULL,\n".
                      "    ".$userID.")\n");
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

    if (mysqli_query($mysql_connection,
                     "DELETE\n".
                     "FROM `message`\n".
                     "WHERE `user_id`=".$userID) !== true)
    {
        return -2;
    }

    return 0;
}

?>
