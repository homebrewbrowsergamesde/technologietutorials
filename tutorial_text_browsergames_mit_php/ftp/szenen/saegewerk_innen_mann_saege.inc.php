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
 * @file $/szenen/saegewerk_innen_mann_saege.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-17
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



$statusAbloesung = getUserVariables($_SESSION['user_id'],
                                    array("saegewerk_innen_mann_saege_konversationsstatus"));

if (is_array($statusAbloesung) === true)
{
    $statusAbloesung = $statusAbloesung['saegewerk_innen_mann_saege_konversationsstatus'];
}



if (isset($_POST['wahl']) != true)
{
    switch ($statusAbloesung)
    {
    case "1":
        echo "<p>\n".
             "  Der Mann scheint weiterhin sehr beschäftigt...\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Den Mann nicht weiter stören."));
        break;
        
    case "2":
    case "3":
    case "4":
        echo "<p>\n".
             "  Der Mann sagt genervt: &bdquo;Jetzt nicht, ich habe doch gerade erst angefangen!&ldquo;".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Den Mann nicht weiter stören."));
        break;

    default:
        echo "<p>\n".
             "  Der Mann an der Säge sagt laut, um die Geräusche zu übertönen: &bdquo;Bitte\n".
             "  stör&rsquo; mich nicht bei der Arbeit! Und sag&rsquo; meinem Kollegen, dass\n".
             "  er mich endlich ablösen soll.&ldquo;\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Den Mann nicht weiter stören."));
        break;
    }
}
else
{
    switch($_POST['wahl'])
    {
    case "fertig":
        switch($statusAbloesung)
        {
        case "0":
            if (setUserVariables($_SESSION['user_id'],
                array("saegewerk_innen_mann_saege_konversationsstatus" => "1")) === 0)
            {
                setScene($_SESSION['user_id'], "saegewerk_innen");
            }
                
            break;

        default:
            setScene($_SESSION['user_id'], "saegewerk_innen");
            break;
        }

        break;
    }

    unset($_POST['wahl']);
}



?>
