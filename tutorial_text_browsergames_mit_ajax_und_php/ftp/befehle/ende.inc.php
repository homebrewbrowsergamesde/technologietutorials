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
 * @file $/befehle/ende.inc.php
 * @author Stephan Kreutzer
 * @since 2012-09-26
 */


 
require_once("commandlib.inc.php");



$commandRoot = new Command("ROOT", "Unbekannt");
$commandRoot->AddSubCommand(new Command("BEENDEN", "Beenden"));
$commandRoot->AddSubCommand(new Command("HILFE", "Hilfe"));



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
    $output = "BEENDEN - HILFE.";
}



?>
