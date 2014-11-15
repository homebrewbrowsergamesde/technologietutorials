<?php
/* Copyright (C) 2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Text-Browsergames mit PHP".
 *
 * Tutorial "Text-Browsergames mit PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Text-Browsergames mit PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Text-Browsergames mit PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/szenen/ruine.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-18
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



$statusSchloss = getUserVariables($_SESSION['user_id'],
                                  array("ruine_schloss_geoeffnet"));

if (is_array($statusSchloss) === true)
{
    $statusSchloss = $statusSchloss['ruine_schloss_geoeffnet'];
}

if (isset($_POST['inventory_item']) === true &&
    $statusSchloss == "0")
{
    require_once(dirname(__FILE__)."/../inventorylib.inc.php");

    if ($_POST['inventory_item'] == INVENTORY_SCHLUESSEL)
    {
        $inventory = new Inventory($_SESSION['user_id']);

        $schluessel = 0;

        if ($inventory->GetItem(INVENTORY_SCHLUESSEL, $schluessel) == true)
        {
            if ($schluessel > 0)
            {
                if (setUserVariables($_SESSION['user_id'],
                                     array("ruine_schloss_geoeffnet" => "1")) === 0)
                {
                    $statusSchloss = "1";
                
                    echo "<p>\n".
                         "  Das Vorhängeschloss springt mit knarzendem Geräusch auf. Mit Leichtigkeit\n".
                         "  sackt die Kette ab und gibt das Gitter frei.\n".
                         "</p>\n";
                }
            }
        }
    }
}



if (isset($_POST['wahl']) != true)
{
    switch ($statusSchloss)
    {
    case "0":
        echo "<p>\n".
             "  Am Ende des Weges ragt eine alte, verfallene Ruine zwischen zwei Hügeln hervor. Obwohl\n".
             "  schon weite Teile der Gebäudemauer eingestürzt sind, führt ein vergitterter Einstieg\n".
             "  in den Keller. Das Gitter wird lediglich von einer Eisenkette mit Vorhängeschloss\n".
             "  zugehalten, die Treppen nach unten sind aber ohne Licht nur schwach erkennbar.\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("zurueck" => "Umkehren."));
        break;
        
    case "1":
        echo "<p>\n".
             "  An der alten, verfallenen Ruine führt ein Einstieg in den Keller. Die Treppen nach\n".
             "  unten sind aber ohne Licht nur schwach erkennbar.\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("betreten" => "Einstieg hinabgehen.",
                                          "zurueck" => "Umkehren."));
        break;
    }
}
else
{
    switch($_POST['wahl'])
    {
    case "zurueck":
        setScene($_SESSION['user_id'], "start");
        break;

    case "betreten":
        if ($statusSchloss == "1")
        {
            setScene($_SESSION['user_id'], "gewonnen");
        }
        else
        {
            // Hier ein Betrugsversuch, da die Option "betreten" nur vorhanden
            // sein kann, wenn $statusSchloss bereits "1" ist.
        }
        
        break;

    default:
        break;
    }

    unset($_POST['wahl']);
}



?>
