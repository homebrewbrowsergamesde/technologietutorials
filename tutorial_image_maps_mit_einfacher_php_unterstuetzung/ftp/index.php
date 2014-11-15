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
 * @file $/index.php
 * @author Stephan Kreutzer
 * @since 2011-08-19
 */



session_start();

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

if (isset($_POST['name']) != true)
{
    echo "        <div>\n".
         "          <h2>Login</h2>\n".
         "        </div>\n".
         "\n".
         "        <form action=\"index.php\" method=\"post\">\n".
         "          <input name=\"name\" type=\"text\" size=\"20\" maxlength=\"40\" /> Name.<br />\n".
         "          <input type=\"submit\" value=\"Submit\" /><br />\n".
         "        </form>\n".
         "\n".
         "        <pre>\n".
         "Tutorial \"Image-Maps mit einfacher PHP-Unterstützung\" (C) 2011-2012  Stephan Kreutzer\n".
         "\n".
         "Tutorial \"Image-Maps mit einfacher PHP-Unterstützung\" is free software: you can redistribute it and/or modify\n".
         "it under the terms of the GNU Affero General Public License version 3 only,\n".
         "as published by the Free Software Foundation.\n".
         "\n".
         "Tutorial \"Image-Maps mit einfacher PHP-Unterstützung\" is distributed in the hope that it will be useful,\n".
         "but WITHOUT ANY WARRANTY; without even the implied warranty of\n".
         "MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the\n".
         "GNU Affero General Public License 3 for more details.\n".
         "\n".
         "You should have received a copy of the GNU Affero General Public License 3\n".
         "along with Tutorial \"Image-Maps mit einfacher PHP-Unterstützung\". If not, see &lt;<a href=\"http://www.gnu.org/licenses/\">http://www.gnu.org/licenses/</a>&gt;.\n".
         "\n".
         "The complete source code is available at &lt;<a href=\"http://www.skreutzer.de/browsergames/technologien/image_maps_mit_einfacher_php_unterstuetzung.html\">http://www.skreutzer.de/browsergames/technologien/image_maps_mit_einfacher_php_unterstuetzung.html</a>&gt;.\n".
         "        </pre>\n";
}
else
{
    require_once("database.inc.php");
    
    $name = false;

    if (isset($_POST['name']) === true)
    {
        // Benutzer-Eingaben müssen _IMMER_ (!!!) erst bearbeitet
        // werden, bevor sie in ein SQL-Query eingefügt werden dürfen.
        // Es könnten unerlaubte Zeichen enthalten sein mit dem Ziel,
        // die Datenbank zu manipulieren.
        $name = mysql_real_escape_string($_POST['name']);
    }

    $user = false;
    
    if ($name != false &&
        $mysql_connection != false)
    {
        $user = mysql_query("SELECT `id`\n".
                            "FROM `users`\n".
                            "WHERE `name` LIKE '".$name."'",
                            $mysql_connection);
    }
    
    if ($user != false)
    {
        if (mysql_num_rows($user) == 0)
        {
            // Den Benutzer gibt es noch nicht, also anlegen.
            mysql_query("INSERT INTO `users` (`id`,\n".
                        "    `name`)\n".
                        "VALUES (NULL,\n".
                        "    '".$name."')\n",
                        $mysql_connection);
                        
            $id = mysql_insert_id($mysql_connection);
            
            if ($id != 0)
            {
                $user = array("id" => $id);
                
                // Initialisierung der Spieler-Daten.
                mysql_query("INSERT INTO `inventory` (`user_id`,\n".
                            "    `aepfel`,\n".
                            "    `burgschluessel`)\n".
                            "VALUES (".$id.",\n".
                            "    0,\n".
                            "    0)\n",
                            $mysql_connection);
            }
        }
        else
        {
            // Den Benutzer gibt es bereits, er will sich
            // wiederholt anmelden.

            $result = mysql_fetch_assoc($user);
            mysql_free_result($user);
            $user = $result;
        }
    }

    if (is_array($user) === true)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $name;

        echo "        <div>\n".
             "          Login erfolgreich. Hier <a href=\"ansicht_1.php\">weiter</a>.\n".
             "        </div>\n";
    }
    else
    {
        echo "        <div>\n".
             "          Problem beim Zugriff auf die Datenbank.\n".
             "        </div>\n";
    }
}
     
echo "    </body>\n".
     "</html>\n".
     "\n";



?>