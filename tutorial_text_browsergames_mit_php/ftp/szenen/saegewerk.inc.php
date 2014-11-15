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
 * @file $/szenen/saegewerk.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-14
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



if (isset($_POST['wahl']) != true)
{
    echo "<p>\n".
         "  Auf einer Ebene sind zahlreiche Holzstapel und Baumstamm-Haufen verteilt.\n".
         "  Am Waldrand steht ein Sägewerk; rechts davon fließt ein Bach vorbei, der aus\n".
         "  dem Wald kommt. Der Bach ist zu breit, um darüberspringen zu können. Ein\n".
         "  Weg führt links den Waldrand entlang und ein anderer einen kleinen Abhang hinab.\n".
         "</p>\n".
         generateHTMLChooseForm("wahl",
                                array("saegewerk" => "Sägewerk betreten.",
                                      "links" => "Den Weg nach links wählen",
                                      "abhang" => "Den Abhang hinabgehen."));
}
else
{
    switch($_POST['wahl'])
    {
    case "saegewerk":
        if (setScene($_SESSION['user_id'], "saegewerk_innen") === 0)
        {
            echo "<p>\n".
                 "  Du betrittst das Sägewerk. Hier ist es etwas düster, es riecht nach Späne\n".
                 "  und es ist ziemlich laut.\n".
                 "</p>\n";
        }

        break;

    case "links":
        setScene($_SESSION['user_id'], "dorf");
        break;

    case "abhang":
        if (setScene($_SESSION['user_id'], "start") === 0)
        {
            echo "<p>\n".
                 "  Du gehst einen kleinen Abhang hinab.\n".
                 "</p>\n";
        }

        break;
    }

    unset($_POST['wahl']);
}



?>
