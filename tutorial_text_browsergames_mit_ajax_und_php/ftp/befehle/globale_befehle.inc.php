<?php
/* Copyright (C) 2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Text-Browsergames mit AJAX und PHP".
 *
 * Tutorial "Text-Browsergames mit AJAX und PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Text-Browsergames mit AJAX und PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Text-Browsergames mit AJAX und PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/befehle/globale_befehle.inc.php
 * @author Stephan Kreutzer
 * @since 2012-09-26
 */



require_once("commandlib.inc.php");



$commandRoot = new Command("ROOT", "Unbekannt");
$commandRoot->AddSubCommand(new Command("BEENDEN", "Beenden"));
$commandBenutze = $commandRoot->AddSubCommand(new Command("BENUTZE", "Benutze"));
$commandRoot->AddSubCommand(new Command("HILFE", "Hilfe"));
$commandRoot->AddSubCommand(new Command("INVENTAR", "Inventar"));
$commandNehme = $commandRoot->AddSubCommand(new Command("NEHME", "Nehme"));

// Handler fuer globale Befehle.

function CMDHANDLER_Unbekannt($args, &$output)
{
    if (count($args) > 0)
    {
        $output = "Unbekannter Befehl '".$args[0]."'.";
    }
    else
    {
        // Wenn CMDHANDLER_UnknownCommand fuer nicht
        // implementierte Befehle verwendet wird.
        $output = "Ohne Effekt.";
    }
}

function CMDHANDLER_Benutze($args, &$output)
{
    if (count($args) >= 2)
    {
        switch ($args[0])
        {
        case "GUMMIPS":
        case "RÖÄCTI":
        case "TULUZAMBACCA":
            if (is_numeric($args[1]) !== true)
            {
                $output = "&lt;Anzahl&gt; ist keine Zahl.";
                return;
            }

            BenutzeFrucht($args[0], $args[1], $output);
            break;
        default:
            $output = "Das geht hier nicht.";
            break;
        }
    }
    else if (count($args) == 1)
    {
        $output = "Wieviel benutzen?";
    }
    else
    {
        $output = "Was soll hier benutzt werden?";
    }
}

function CMDHANDLER_Beenden($args, &$output)
{
    $_SESSION = array();

    if (isset($_COOKIE[session_name()]) == true)
    {
        setcookie(session_name(), '', time()-42000, '/');
    }

    $output = "Beendet.";
}

function CMDHANDLER_Hilfe($args, &$output)
{
    $output = "BEENDEN - BENUTZE &lt;Gegenstand&gt; &lt;Anzahl&gt; - GEHE &lt;Ziel&gt; - HILFE - INVENTAR - NEHME &lt;Gegenstand&gt; &lt;Anzahl&gt;.";
}

function CMDHANDLER_Inventar($args, &$output)
{
    require_once("database.inc.php");

    if ($mysql_connection != false)
    {
        $result = mysql_query("SELECT `name`,\n".
                              "    `amount`\n".
                              "FROM `inventory`\n".
                              "WHERE `user_id`=".$_SESSION['user_id']."\n".
                              "ORDER BY `name`\n",
                              $mysql_connection);

        if ($result !== false)
        {
            if (@mysql_num_rows($result) > 0)
            {
                $itemCount = 0;

                while (1)
                {
                    if (($row = @mysql_fetch_array($result, MYSQL_ASSOC)) != false)
                    {
                        if ($row['amount'] > 0)
                        {
                            $output .= $row['amount']."x".$row['name']." - ";
                            $itemCount++;
                        }
                    }
                    else
                    {
                        break;
                    }
                }

                if ($itemCount > 0)
                {
                    $output = substr($output, 0, -3);
                    $output .= ".";
                }
                else
                {
                    $output = "Nichts.";
                }
            }
            else
            {
                $output = "Nichts.";
            }

            mysql_free_result($result);
        }
    }
}

function CMDHANDLER_Nehme($args, &$output)
{
    if (count($args) >= 1)
    {
        $output = $args[0]." kann hier nicht genommen werden.";
    }
    else
    {
        $output = "Was soll hier genommen werden?";
    }
}



function BenutzeFrucht($frucht, $anzahl, &$output)
{
    require_once(dirname(__FILE__)."/../inventorylib.inc.php");
    $inventar = new Inventory($_SESSION['user_id']);

    $bestand = 0;

    if ($inventar->GetItem($frucht, $bestand) !== true)
    {
        $output = "Das geht hier nicht.";
        return;
    }

    if ((int)$bestand <= 0)
    {
        $output = "Das geht hier nicht.";
        return;
    }
    
    if ($anzahl > $bestand)
    {
        $output = "Du kannst nur ".$bestand."x".$frucht." benutzen.";
        return;
    }

    $inventar->SetItem($frucht, $bestand - $anzahl);


    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    setUserVariables($_SESSION['user_id'], array("status_start" => "2"));
    
    switch ($frucht)
    {
    case "GUMMIPS":
        $output = "Mmh, das hat gut geschmeckt, war aber ein bisschen wenig.";
        break;
    case "RÖÄCTI":
        $output = "Mmh, das hat gut geschmeckt!";
        break;
    case "TULUZAMBACCA":
        $output = "Mmh, das hat gut geschmeckt, war aber ein bisschen viel.";
        break;
    }
}



?>
