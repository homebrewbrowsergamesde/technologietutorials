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
 * @file $/szenen/saegewerk_innen_mann_begutachter.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-02
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



$statusAbloesung = getUserVariables($_SESSION['user_id'],
                                    array("saegewerk_innen_mann_saege_konversationsstatus"));

if (is_array($statusAbloesung) === true)
{
    $statusAbloesung = $statusAbloesung['saegewerk_innen_mann_saege_konversationsstatus'];
}


if (isset($_POST['inventory_item']) === true &&
    $statusAbloesung == "2")
{
    require_once(dirname(__FILE__)."/../inventorylib.inc.php");

    if ($_POST['inventory_item'] == INVENTORY_BROETCHEN)
    {
        $inventory = new Inventory($_SESSION['user_id']);
 
        $anzahlBroetchen = 0;
                    
        if ($inventory->GetItem(INVENTORY_BROETCHEN, $anzahlBroetchen) == true)
        {
            if ($anzahlBroetchen > 0)
            {
                $inventory->SetItem(INVENTORY_BROETCHEN, $anzahlBroetchen - 1);

                if (setUserVariables($_SESSION['user_id'],
                                     array("saegewerk_innen_mann_saege_konversationsstatus" => "3")) === 0)
                {
                    $statusAbloesung = "3";
                }
                else
                {
                    $inventory->DiscardModifications();
                }
            }
        }
    }
}



if (isset($_POST['wahl']) != true)
{
    switch ($statusAbloesung)
    {
    case "1":
        echo "<p>\n".
             "  Der Mann meint: &bdquo;Hmmm? Ablösen? Naja...na gut.&ldquo; und geht davon.\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Dem Mann folgen."));

        break;
        
    case "2":
        echo "<p>\n".
             "  Der Mann, der vorher an der Säge stand, sagt: &bdquo;Endlich, meine wohlverdiente Pause! Dumm nur,\n".
             "  dass ich nichts zu Essen mitgenommen habe...&ldquo;\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Den Mann nicht weiter von der Erholung abhalten."));
        break;

    case "3":
        echo "<p>\n".
             "  Der Mann, der vorher an der Säge stand, sagt: &bdquo;Mjam! Super, dass du mir dein Brötchen gibst!\n".
             "  Ich habe jetzt etwas zu vespern; du sollst dafür diesen alten Schlüssel bekommen.&ldquo;\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Mitnehmen und weggehen."));
        break;

    case "4":
        echo "<p>\n".
             "  Der Mann, der vorher an der Säge stand, sagt: &bdquo;Vielen Dank nochmal für das Brötchen!&ldquo;\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Weggehen."));
        break;
       
    default:
        echo "<p>\n".
             "  Der Mann reagiert gar nicht, sondern widmet sich ganz den\n".
             "  Baumstämmen.\n".
             "</p>\n".
             generateHTMLChooseForm("wahl",
                                    array("fertig" => "Aufgeben, Aufmerksamkeit zu erregen."));
        break;
    }
}
else
{
    if ($_POST['wahl'] == "fertig")
    {
        switch ($statusAbloesung)
        {
        case "1":
            if (setUserVariables($_SESSION['user_id'],
                                 array("saegewerk_innen_mann_saege_konversationsstatus" => "2")) === 0)
            {
                setScene($_SESSION['user_id'], "saegewerk_innen");
            }
            
            break;

        case "3":
            // Schluessel nehmen.
            
            {
                require_once(dirname(__FILE__)."/../inventorylib.inc.php");

                $inventory = new Inventory($_SESSION['user_id']);
                $inventory->SetItem(INVENTORY_SCHLUESSEL, 1);
            
                if (setUserVariables($_SESSION['user_id'],
                                     array("saegewerk_innen_mann_saege_konversationsstatus" => "4")) === 0)
                {
                    setScene($_SESSION['user_id'], "saegewerk_innen");
                }
                else
                {
                    $inventory->DiscardModifications();
                }
            }

            break;

        default:
            setScene($_SESSION['user_id'], "saegewerk_innen");
            break;
        }
    }

    unset($_POST['wahl']);
}



?>
