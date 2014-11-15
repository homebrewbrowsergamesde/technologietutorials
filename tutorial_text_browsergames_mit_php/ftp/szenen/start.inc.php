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
 * @file $/szenen/start.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-17
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



if (isset($_POST['richtung']) != true)
{
    echo "<p>\n".
         "  Du stehst an einer Kreuzung. Ein alter Wegweiser deutet nach rechts\n".
         "  mit der Aufschrift &bdquo;Sägewerk&ldquo;.\n".
         "</p>\n".
         generateHTMLChooseForm("richtung",
                                array("links" => "Links entlang gehen.",
                                      "geradeaus" => "Geradeaus gehen.",
                                      "rechts" => "Rechts entlang gehen."));
}
else
{
    switch($_POST['richtung'])
    {
    case "links":
        setScene($_SESSION['user_id'], "ruine");
        break;

    case "geradeaus":
        setScene($_SESSION['user_id'], "dorf");
        break;

    case "rechts":
        if (setScene($_SESSION['user_id'], "saegewerk") === 0)
        {
            echo "<p>\n".
                 "  Du gehst einen kleinen Abhang hinauf.\n".
                 "</p>\n";
        }

        break;

    default:
        break;
    }

    unset($_POST['richtung']);
}

?>
