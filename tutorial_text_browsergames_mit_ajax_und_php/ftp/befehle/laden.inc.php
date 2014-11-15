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
 * @file $/befehle/laden.inc.php
 * @author Stephan Kreutzer
 * @since 2012-07-14
 */



require_once(dirname(__FILE__)."/globale_befehle.inc.php");



$commandGehe = $commandRoot->AddSubCommand(new Command("GEHE", "Gehe"));
$commandGeheAusgang = $commandGehe->AddSubCommand(new Command("AUSGANG", "GeheAusgang"));
$commandNehme->AddSubCommand(new Command("GUMMIPS", "NehmeFrucht1"));
$commandNehme->AddSubCommand(new Command("RÖÄCTI", "NehmeFrucht2"));
$commandNehme->AddSubCommand(new Command("TULUZAMBACCA", "NehmeFrucht3"));
$commandNehme->AddSubCommand(new Command("TULUZAMBACCA-FRUCHT", "NehmeFrucht3"));



function CMDHANDLER_Gehe($args, &$output)
{
    if (count($args) > 0)
    {
        $output = "Dort geht es nicht entlang.";
    }
    else
    {
        $output = "Wohin bitte?";
    }
}

function CMDHANDLER_GeheAusgang($args, &$output)
{
    $output = "Die Türen öffnen sich von selbst.";
    $description = NULL;

    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    if (setScene($_SESSION['user_id'], "platz", $description) === 0)
    {
        $output .= " ".$description;
    }
}

function CMDHANDLER_NehmeFrucht1($args, &$output)
{
    return NehmeLaden("GUMMIPS", $args, $output);
}

function CMDHANDLER_NehmeFrucht2($args, &$output)
{
    return NehmeLaden("RÖÄCTI", $args, $output);
}

function CMDHANDLER_NehmeFrucht3($args, &$output)
{
    return NehmeLaden("TULUZAMBACCA", $args, $output);
}

function NehmeLaden($frucht, $args, &$output)
{
    require_once(dirname(__FILE__)."/../inventorylib.inc.php");
    $inventar = new Inventory($_SESSION['user_id']);

    $geld = 0;

    if ($inventar->GetItem("SPACENTS", $geld) !== true)
    {
        $output = "Dein Geldbeutel ist defekt.";
        return;
    }

    if ((int)$geld <= 0)
    {
        $output = $frucht." kann hier nicht genommen werden.";
        return;
    }
    
    $anzahl = 1;
    
    if (count($args) >= 1)
    {
        if (is_numeric($args[0]) === true)
        {
            $anzahl = (int)$args[0];
        }
    }
    
    $kosten = 0;
    
    switch ($frucht)
    {
    case "GUMMIPS":
        $kosten = $anzahl * 1;
        break;
    case "RÖÄCTI":
        $kosten = $anzahl * 2;
        break;
    case "TULUZAMBACCA":
        $kosten = $anzahl * 3;
        break;
    }
    
    if ($kosten > $geld)
    {
        $output = $anzahl."x".$frucht." kostet ".$kosten." Spacents, du verfügst aber nur über ".$geld." Spacents.";
        return;
    }

    $geld -= $kosten;
    $inventar->SetItem("SPACENTS", $geld);

    $bestand = 0;
    $inventar->GetItem($frucht, $bestand);

    if ($bestand > 0)
    {
        $inventar->SetItem($frucht, $bestand + $anzahl);
    }
    else
    {
        $inventar->AddItems(array($frucht => $anzahl));
    }
       
    $output = "Du hast ".$anzahl."x".$frucht." erhalten.";


    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    $geldBank = getGlobalVariables(array("bank_geldsumme"));
       
    if (is_array($geldBank) !== true)
    {
        return;
    }

    $geldBank = (int)$geldBank['bank_geldsumme'];
    $geldBank += $kosten;
    setGlobalVariables(array("bank_geldsumme" => (string)$geldBank));
}



?>
