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
 * @file $/befehle/start.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-19
 */



require_once(dirname(__FILE__)."/globale_befehle.inc.php");



$commandGehe = $commandRoot->AddSubCommand(new Command("GEHE", "Gehe"));
$commandGehe->AddSubCommand(new Command("OSTEN", "GeheOsten"));
$commandGehe->AddSubCommand(new Command("FLUGHAFEN", "GeheFlughafen"));



function CMDHANDLER_Gehe($args, &$output)
{
    if (count($args) > 0)
    {
        $output = $args[0]." ist keine Richtung.";
    }
    else
    {
        $output = "Wohin?";
    }
}

function CMDHANDLER_GeheOsten($args, &$output)
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    setScene($_SESSION['user_id'], "platz", $output);
}

function CMDHANDLER_GeheFlughafen($args, &$output)
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    $statusBesuch = getUserVariables($_SESSION['user_id'], array("status_start"));

    if (is_array($statusBesuch) === true)
    {
        if ($statusBesuch['status_start'] == "2")
        {
            $description = "";

            if (setScene($_SESSION['user_id'], "ende", $description) === 0)
            {
                $output = "Mit neuer Energie und gut gefülltem Bauch trittst du die Reise zum nächstbesten Planeten an, um endlich von diesem öden Ort wegzukommen. ".$description;
            }
            else
            {
                $output = "Offenbar sind die Türen verschlossen...";
            }
        }
        else
        {
            $output = "Du bist viel zu hungrig, um eine Reise anzutreten.";
        }
    }
}



?>
