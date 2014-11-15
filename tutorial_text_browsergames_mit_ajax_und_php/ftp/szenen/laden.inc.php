<?php
/* Copyright (C) 2012  Stephan Kreutzer, Jan-Hinrich Behne
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
 * @file $/szenen/laden.inc.php
 * @author Stephan Kreutzer
 * @since 2012-07-14
 */



function DEFAULTHANDLER_Description()
{
    $output = "Du befindest dich in einem kleinen Obst- und Gemüse-Geschäft.";

    require_once(dirname(__FILE__)."/../inventorylib.inc.php");

    $inventar = new Inventory($_SESSION['user_id']);
    $geld = 0;

    $inventar->GetItem("SPACENTS", $geld);

    if ($geld > 0)
    {
        $output .= " Da du etwas Bargeld bei dir führst, könntest du entweder Gummips, Röäcti oder eine besonders große Tuluzambacca-Frucht kaufen. Alternativ kannst du auch den Laden über den Ausgang verlassen.";
    }
    else
    {
        $output .= " Während du dir das Sortiment ansiehst, stellst du fest, dass du kein Bargeld bei dir trägst. Es bleibt wohl nichts anderes übrig, als den Ausgang aufzusuchen.";
    }

    return $output;
}



?>
