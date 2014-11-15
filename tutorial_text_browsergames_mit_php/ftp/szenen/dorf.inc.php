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
 * @file $/szenen/dorf.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-14
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



if (isset($_POST['wahl']) != true)
{
    echo "<p>\n".
         "  Ein kleines Dorf bestehend aus drei Häusern liegt am Wegesrand.\n".
         "  Neben zwei kleineren Wohnhäusern befindet sich im Erdgeschoss des\n".
         "  dritten Hauses ein kleiner Laden. Der Weg biegt hinter dem Dorf\n".
         "  nach rechts ab, da dort dann der Wald beginnt.\n".
         "</p>\n".
         generateHTMLChooseForm("wahl",
                                array("laden" => "Den Laden betreten.",
                                      "rechts" => "Rechts den Waldrand entlanggehen.",
                                      "runter" => "Dorf verlassen."));
}
else
{
    switch($_POST['wahl'])
    {
    case "laden":
        if (setScene($_SESSION['user_id'], "dorf_laden") === 0)
        {
            echo "<p>\n".
                 "  Beim Betreten des Ladens erschellt die Türglocke und eine\n".
                 "  Frau kommt aus den Geschäftsräumen hervor.\n".
                 "</p>\n";
        }

        break;

    case "rechts":
        setScene($_SESSION['user_id'], "saegewerk");
        break;

    case "runter":
        setScene($_SESSION['user_id'], "start");
        break;

    default:
        break;
    }

    unset($_POST['wahl']);
}

?>
