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
 * @file $/befehle/platz.inc.php
 * @author Stephan Kreutzer
 * @since 2012-07-14
 */



require_once(dirname(__FILE__)."/globale_befehle.inc.php");



$commandGehe = $commandRoot->AddSubCommand(new Command("GEHE", "Gehe"));
$commandGeheLaden = $commandGehe->AddSubCommand(new Command("LADEN", "GeheLaden"));
$commandGehe->AddSubCommand(new Command("GEMÜSE-LADEN", "GeheLaden"));
$commandGehe->AddSubCommand(new Command("OBST-LADEN", "GeheLaden"));
$commandGehe->AddSubCommand(new Command("WESTEN", "GeheWesten"));
$commandGehe->AddSubCommand(new Command("Norden", "GeheNorden"));



function CMDHANDLER_Gehe($args, &$output)
{
    if (count($args) > 0)
    {
        $output = $args[0]." ist kein Ziel, wohin man gehen könnte.";
    }
    else
    {
        $output = "Wohin?";
    }
}

function CMDHANDLER_GeheLaden($args, &$output)
{
    $output = "Die Türen öffnen sich automatisch.";
    $description = NULL;

    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    if (setScene($_SESSION['user_id'], "laden", $description) === 0)
    {
        $output .= " ".$description;
    }
}

function CMDHANDLER_GeheWesten($args, &$output)
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    setScene($_SESSION['user_id'], "start", $output);
}

function CMDHANDLER_GeheNorden($args, &$output)
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    setScene($_SESSION['user_id'], "bank", $output);
}



?>
