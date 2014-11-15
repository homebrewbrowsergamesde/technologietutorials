<?php
/* Copyright (C) 2011-2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Image-Maps mit einfacher PHP-Unterstützung".
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Image-Maps mit einfacher PHP-Unterstützung" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Image-Maps mit einfacher PHP-Unterstützung". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/ansicht_3.php
 * @author Stephan Kreutzer
 * @since 2011-08-03
 */



$session = session_start();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
     "    <head>\n".
     "        <title>Spielwelt</title>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\" />\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\" />\n".
     "    </head>\n".
     "    <body>\n";

if ($session === true)
{
    $session = isset($_SESSION['user_id']);
}

if ($session !== true)
{
    echo "        <div>\n".
         "          Bitte erst einloggen.\n".
         "        </div>\n".
         "    </body>\n".
         "</html>\n";

    exit();
}



require_once("database.inc.php");

$inventar = false;

if ($mysql_connection != false)
{
    $inventar = mysql_query("SELECT `aepfel`,\n".
                            "    `burgschluessel`\n".
                            "FROM `inventory`\n".
                            "WHERE `user_id`=".$_SESSION['user_id']."\n",
                            $mysql_connection);
}
    
if ($inventar != false)
{
    $result = mysql_fetch_assoc($inventar);
    mysql_free_result($inventar);
    $inventar = $result;
}

if ($inventar != false)
{
    $variante = 1;


    // Logik.

    if (isset($_GET['action']) === true)
    {
        if ($_GET['action'] === "give_apples")
        {
            if ($inventar['aepfel'] >= 3)
            {
                if (mysql_query("UPDATE `inventory`\n".
                                "SET `aepfel`=`aepfel` - 3\n".
                                "WHERE `user_id`=".$_SESSION['user_id']."\n",
                                $mysql_connection) === true)
                {
                    $variante = 3;

                    if ($inventar['burgschluessel'] == 0)
                    {
                        if (mysql_query("UPDATE `inventory`\n".
                                        "SET `burgschluessel`=1\n".
                                        "WHERE `user_id`=".$_SESSION['user_id']."\n",
                                        $mysql_connection) === true)
                        {
                            $variante = 4;
                        }
                    }
                    
                    if ($inventar['burgschluessel'] == 1)
                    {
                        // Aepfel bereits schonmal gegeben, aber Schluessel nicht genommen.
                        $variante = 4;
                    }
                }
            }
            else
            {
                $variante = 2;
            }
        }
        else if ($_GET['action'] === "get_key")
        {
            if ($inventar['burgschluessel'] == 1)
            {
                if (mysql_query("UPDATE `inventory`\n".
                                "SET `burgschluessel`=2\n".
                                "WHERE `user_id`=".$_SESSION['user_id']."\n",
                                $mysql_connection) === true)
                {
                    $variante = 3;
                }
            }
            else
            {
                // Hier versucht jemand unerlaubterweise, mit "get_key" aufzurufen,
                // obwohl er noch keine 3 Aepfel gegeben oder den Schluessel bereits
                // genommen hat.
            }
        }
    }
    
    // $inventar evtl. mit alten Werten!
    

    // Ansicht.

    echo "        <img src=\"ansicht_3_".$variante.".png\" border=\"1\" alt=\"Mann\" usemap=\"#default\"/>\n".
         "        <map name=\"default\">\n".
         //              Bewegen geht immer.
         "          <area shape=\"poly\" coords=\"799,384,799,599,709,599,709,367\" href=\"ansicht_1.php\" alt=\"Rechts entlang\" title=\"Rechts entlang\" />\n";

    switch ($variante)
    {
    case 1:
        echo "          <area shape=\"poly\" coords=\"384,145,440,136,496,146,525,169,533,190,522,214,480,237,430,240,377,228,346,202,353,166\" href=\"ansicht_3.php?action=give_apples\" alt=\"Ich habe Hunger...\" title=\"Äpfel geben\" />\n";
        break;
    case 2:
        // Zu wenig Aepfel.
        break;
    case 3:
        // Nur Dank.
        break;
    case 4:
        // Dank und Schluessel.
        echo "          <area shape=\"poly\" coords=\"449,305,444,304,439,309,440,315,445,318,452,313,451,308,460,298,463,301,463,297,467,296,461,290\" href=\"ansicht_3.php?action=get_key\" alt=\"Schlüssel\" title=\"Schlüssel nehmen\" />\n";
        break;
    default:
        // Unbekannte Variante.
        break;
    }

    echo "        </map>\n";
}
else
{
    echo "        <div>\n".
         "          Problem beim Zugriff auf die Datenbank.\n".
         "        </div>\n";
}

echo "    </body>\n".
     "</html>\n".
     "\n";



?>