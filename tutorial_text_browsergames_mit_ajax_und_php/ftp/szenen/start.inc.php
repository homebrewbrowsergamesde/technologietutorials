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
 * @file $/szenen/start.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-19
 */



function DEFAULTHANDLER_Description()
{
    $text = "";

    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    $statusBesuch = getUserVariables($_SESSION['user_id'], array("status_start"));

    if (is_array($statusBesuch) === true)
    {
        if ($statusBesuch['status_start'] == "0")
        {
            $text .= "Du wachst auf einer schmalen Bank in der Nähe eines Space-Flughafens auf. Du hast keine Erinnerung an die letzten Stunden, verspührst aber etwas Hunger. ";
            
            setUserVariables($_SESSION['user_id'], array("status_start" => "1"));
        }
        else if ($statusBesuch['status_start'] == "1")
        {
            $text .= "Der trostlose Park vor dem Space-Flughafen. Du hast immer noch etwas Hunger. ";
        }
        else if ($statusBesuch['status_start'] == "2")
        {
            $text .= "Der trostlose Park vor dem Space-Flughafen. ";
        }
        else
        {
        
        }
    }

    $text .= "Es führt bloß eine Straße Richtung Osten, welche mit einem Schild „Innenstadt“ beschriftet ist. Ferner kann der Flughafen betreten werden.";

    return $text;
}



?>
