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
 * @file $/ansicht_2.php
 * @author Stephan Kreutzer
 * @since 2011-08-19
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

$baum = false;

if ($mysql_connection != false)
{
    $baum = mysql_query("SELECT `zuletzt`\n".
                        "FROM `timer`\n".
                        "WHERE `name` LIKE \"baum\"",
                         $mysql_connection);
}
    
if ($baum != false)
{
    $result = mysql_fetch_assoc($baum);
    mysql_free_result($baum);
    $baum = $result;
}

if ($baum != false)
{
    // Logik.

    if (isset($_GET['action']) === true)
    {
        if ($_GET['action'] === "get_apple")
        {
            if ($baum['zuletzt'] >= date("Y-m-d"))
            {
                // Hier versucht jemand unerlaubterweise, mit "get_apple" aufzurufen,
                // obwohl der Apfel heute schon geholt wurde und die Ansicht keinen
                // Apfel mehr anzeigt.
            }
            else  
            {
                // Bei $baum['zuletzt'] === NULL Apfel erstmalig holen,
                // bei $baum['zuletzt'] < date("Y-m-d") Apfel fuer heute holen.
                
                if (mysql_query("UPDATE `timer`\n".
                                "SET `zuletzt`=CURDATE()\n".
                                "WHERE `name` LIKE \"baum\"\n",
                                $mysql_connection) === true)
                {
                    // Apfel erfolgreich geholt.
                    $baum['zuletzt'] = date("Y-m-d");

                    mysql_query("UPDATE `inventory`\n".
                                "SET `aepfel`=`aepfel` + 1\n".
                                "WHERE `user_id`=".$_SESSION['user_id']."\n",
                                $mysql_connection);
                }
            }
        }
    }


    // Ansicht.

    $variante = 1;

    if ($baum['zuletzt'] === NULL ||
        $baum['zuletzt'] < date("Y-m-d"))
    {
        $variante = rand(2, 4);
    }

    echo "        <img src=\"ansicht_2_".$variante.".png\" border=\"1\" alt=\"Streuobstwiese\" usemap=\"#default\"/>\n".
         "        <map name=\"default\">\n".
         //              Bewegen geht immer.
         "          <area shape=\"poly\" coords=\"0,599,42,599,42,407,0,370\" href=\"ansicht_1.php\" alt=\"Links entlang\" title=\"Links entlang\" />\n";

    switch ($variante)
    {
    case 2:
        echo "          <area shape=\"circle\" coords=\"411,378,8\" href=\"ansicht_2.php?action=get_apple\" alt=\"Apfel\" title=\"Apfel nehmen\" />\n";
        break;
    case 3:
        echo "          <area shape=\"circle\" coords=\"455,360,8\" href=\"ansicht_2.php?action=get_apple\" alt=\"Apfel\" title=\"Apfel nehmen\" />\n";
        break;
    case 4:
        echo "          <area shape=\"circle\" coords=\"414,328,8\" href=\"ansicht_2.php?action=get_apple\" alt=\"Apfel\" title=\"Apfel nehmen\" />\n";
        break;
    default:
        // Kein Apfel.
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