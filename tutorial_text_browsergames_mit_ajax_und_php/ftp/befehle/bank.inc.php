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
 * @file $/befehle/bank.inc.php
 * @author Stephan Kreutzer
 * @since 2012-08-14
 */



require_once(dirname(__FILE__)."/globale_befehle.inc.php");



$commandGehe = $commandRoot->AddSubCommand(new Command("GEHE", "Gehe"));
$commandGeheLaden = $commandGehe->AddSubCommand(new Command("SÜDEN", "GeheSueden"));
$commandBenutzeKarte = $commandBenutze->AddSubCommand(new Command("BANKKARTE", "BenutzeKarte"));



function CMDHANDLER_Gehe($args, &$output)
{
    if (count($args) > 0)
    {
        $output = $args[0]." - Fehlanzeige.";
    }
    else
    {
        $output = "Kein Ziel angegeben.";
    }
}

function CMDHANDLER_GeheSueden($args, &$output)
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    setScene($_SESSION['user_id'], "platz", $output);
}


function CMDHANDLER_BenutzeKarte($args, &$output)
{
    require_once(dirname(__FILE__)."/../inventorylib.inc.php");

    $inventar = new Inventory($_SESSION['user_id']);
    $bankkarte = 0;

    if ($inventar->GetItem("BANKKARTE", $bankkarte) !== true)
    {
        $output = "Der Hyperidentificationsstreifen der Bankkarte ist defekt, sodass der Geldautomat sie nicht lesen kann.";
        return;
    }

    if ((int)$bankkarte <= 0)
    {
        $output = "Ohne Bankkarte kann dieser Geldautomat nicht bedient werden.";
        return;
    }
    
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    $geldBank = getGlobalVariables(array("bank_geldsumme"));
       
    if (is_array($geldBank) !== true)
    {
        $output = "Der Geldautomat ist leider ebenfalls außer Betrieb.";
        return;
    }

    $geldBank = (int)$geldBank['bank_geldsumme'];

    if ($geldBank <= 0)
    {
        $output = "Leider verfügt der Geldautomat über keine weiteren Barreserven mehr.";
        return;
    }
        
    $geldSpieler = 0;
    $inventar->GetItem("SPACENTS", $geldSpieler);

    if ($geldSpieler >= 3)
    {
        $inventar->SetItem("BANKKARTE", "0");    
        $output = "Der Geldautomat hat deine Bankkarte einbehalten! Offenbar wird auf diesem Planeten deine ständige Überziehung nicht geduldet...";
        return;
    }

    $geldSpieler += 1;
    $inventar->SetItem("SPACENTS", (string)$geldSpieler);

    $geldBank -= 1;
    setGlobalVariables(array("bank_geldsumme" => (string)$geldBank));
    
    $output = "Du hast 1 Spacent abgehoben.";
}



?>
