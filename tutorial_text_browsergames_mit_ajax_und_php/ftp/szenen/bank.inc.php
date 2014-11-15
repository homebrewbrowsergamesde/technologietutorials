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
 * @file $/szenen/bank.inc.php
 * @author Stephan Kreutzer
 * @since 2012-08-14
 */



function DEFAULTHANDLER_Description()
{
    require_once(dirname(__FILE__)."/../gamelib.inc.php");

    $geldsumme = getGlobalVariables(array("bank_geldsumme"));

    if (is_array($geldsumme) === true)
    {
        $geldsumme = (string)$geldsumme['bank_geldsumme'];
    }
    else
    {
        $geldsumme = "0";
    }

    return "Die Bank scheint geschlossen zu sein. Lediglich die Geldautomaten stehen zur Verfügung, an welchen alle möglichen Kreaturen ihre Geschäfte tätigen. Vorschriftsgemäß zeigt ein Display die momentan verfügbare Geldsumme an: ".$geldsumme." Spacents. Im Süden führt die Straße zu einem kleinen öffentlichen Platz.";
}



?>
