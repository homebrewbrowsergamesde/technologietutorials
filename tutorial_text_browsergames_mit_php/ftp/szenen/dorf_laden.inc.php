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
 * @file $/szenen/dorf_laden.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-17
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



$broetchen = getGlobalVariables(array("dorf_laden_broetchen_timer",
                                      "dorf_laden_broetchen_anzahl"));

if (is_array($broetchen) === true)
{
    if ($broetchen['dorf_laden_broetchen_timer'] < date("Y-m-d"))
    {
        if (setGlobalVariables(array("dorf_laden_broetchen_timer" => date("Y-m-d"),
                                     "dorf_laden_broetchen_anzahl" => "3")) === 0)
        {
            $broetchen['dorf_laden_broetchen_anzahl'] = 3;
        }
    }
}



if (isset($_POST['wahl']) != true)
{
    if (is_array($broetchen) === true)
    {
        if ($broetchen['dorf_laden_broetchen_anzahl'] > 0)
        {
            echo "<p>\n".
                 "  Die Frau hinter der Theke sagt: &bdquo;Guten Tag! Was\n".
                 "  darf&rsquo;s sein?&ldquo;\n".
                 "</p>\n".
                 generateHTMLLadenForm($broetchen['dorf_laden_broetchen_anzahl']);
        }
        else
        {
            echo "<p>\n".
                 "  Die Frau hinter der Theke sagt: &bdquo;Tut mir leid, wir haben\n".
                 "  leider schon geschlossen...kommen Sie doch einfach morgen wieder!&ldquo;\n".
                 "</p>\n".
                 generateHTMLChooseForm("wahl", array("verlassen" => "Laden verlassen."));
        }
    }
}
else
{
    switch($_POST['wahl'])
    {
    case "broetchen":
        if (isset($_POST['anzahl']) === true)
        {
            $anzahl = (int)$_POST['anzahl'];

            if ($anzahl > $broetchen['dorf_laden_broetchen_anzahl'])
            {
                // Hier kann nicht geschummelt werden: es werden hoechstens
                // so viele Broetchen gekauft, wie auch vorhanden sind.
                $anzahl = $broetchen['dorf_laden_broetchen_anzahl'];
            }
            
            require_once(dirname(__FILE__)."/../inventorylib.inc.php");

            $inventory = new Inventory($_SESSION['user_id']);

            $taler = 0;
            $inventory->GetItem(INVENTORY_TALER, $taler);
            
            if ($taler < $anzahl * 2)
            {
                echo "<p>\n".
                     "  Die Frau sagt: &bdquo;Entschuldigung, aber es fehlen ".(($anzahl * 2) - $taler)." Taler...&ldquo;\n".
                     "</p>\n".
                     generateHTMLLadenForm($broetchen['dorf_laden_broetchen_anzahl']);
            }
            else
            {
                $bisherigeAnzahlBroetchen = 0;
                $inventory->GetItem(INVENTORY_BROETCHEN, $bisherigeAnzahlBroetchen);

                $erfolg = $inventory->SetItem(INVENTORY_BROETCHEN, $bisherigeAnzahlBroetchen + $anzahl);
                
                if ($erfolg === true)
                {
                    $erfolg = $inventory->SetItem(INVENTORY_TALER, $taler - ($anzahl * 2));
                }
                
                if ($erfolg === true)
                {
                    $erfolg = setGlobalVariables(array("dorf_laden_broetchen_anzahl" => $broetchen['dorf_laden_broetchen_anzahl'] - $anzahl));
                }

                if ($erfolg === 0)
                {
                    $broetchen['dorf_laden_broetchen_anzahl'] -= $anzahl;
                
                    echo "<p>\n".
                         "  Die Frau reicht ".$anzahl." Brötchen und bedankt sich höflich.\n".
                         "</p>\n";
                         
                    if ($broetchen['dorf_laden_broetchen_anzahl'] > 0)
                    {
                        echo generateHTMLLadenForm($broetchen['dorf_laden_broetchen_anzahl']);
                    }
                    else
                    {
                        echo generateHTMLChooseForm("wahl", array("verlassen" => "Laden verlassen."));
                    }
                }
                else
                {
                    echo "<p>\n".
                         "  Die Frau sagt: &bdquo;Oh, wie ungeschickt von mir...das ging gerade daneben.&ldquo;\n".
                         "</p>\n".
                         generateHTMLLadenForm($broetchen['dorf_laden_broetchen_anzahl']);
                }
            }
        }

        break;

    case "verlassen":
        if (setScene($_SESSION['user_id'], "dorf") === 0)
        {
            echo "<p>\n".
                 "  Beim Verlassen des Ladens erschellt die Türglocke.\n".
                 "</p>\n";
        }

        break;

    default:
        break;
    }

    unset($_POST['wahl']);
}

function generateHTMLLadenForm($anzahlBroetchen)
{
    $form = "<form action=\"game.php\" method=\"post\">\n".
            "  <div>\n".
            "    <input type=\"radio\" name=\"wahl\" value=\"broetchen\" />Belegte Brötchen (je 2 Taler das Stück).<br />\n".
            "    <!-- input type=\"radio\" name=\"wahl\" value=\"aepfel\" />Äpfel (je 1 Taler das Stück).<br / -->\n".
            "    <select name=\"anzahl\" size=\"1\">\n";

    for ($i = 1; $i <= $anzahlBroetchen; $i++)
    {
        $form .= "      <option>".$i."</option>\n";
    }

    $form .= "    </select>\n".
             "  </div>\n".
             "  <div>\n".
             "    <input type=\"radio\" name=\"wahl\" value=\"verlassen\" />Laden verlassen.<br />\n".
             "  </div>\n".
             "  <input type=\"submit\" value=\"OK\" /><br />\n".
             "</form>\n";

    return $form;
}



?>
