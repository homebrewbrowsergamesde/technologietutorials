<?php
/* Copyright (C) 2012-2013  Stephan Kreutzer
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
 * @file $/index.php
 * @author Stephan Kreutzer
 * @since 2012-06-14
 */



session_start();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
     "    <head>\n".
     "        <title>Textgame</title>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\" />\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\" />\n".
     "    </head>\n".
     "    <body>\n";

if (isset($_POST['name']) !== true ||
    isset($_POST['passwort']) !== true)
{
    echo "        <form action=\"index.php\" method=\"post\">\n".
         "          <input name=\"name\" type=\"text\" size=\"20\" maxlength=\"40\" /> Name.<br />\n".
         "          <input name=\"passwort\" type=\"password\" size=\"20\" maxlength=\"40\" /> Passwort.<br />\n".
         "          <input type=\"submit\" value=\"OK\" /><br />\n".
         "        </form>\n".
         "\n".
         "        <pre>\n".
         "Tutorial \"Text-Browsergames mit AJAX und PHP\" (C) 2012-2013  Stephan Kreutzer, Jan-Hinrich Behne\n".
         "\n".
         "Tutorial \"Text-Browsergames mit AJAX und PHP\" is free software: you can redistribute it and/or modify\n".
         "it under the terms of the GNU Affero General Public License version 3 only,\n".
         "as published by the Free Software Foundation.\n".
         "\n".
         "Tutorial \"Text-Browsergames mit AJAX und PHP\" is distributed in the hope that it will be useful,\n".
         "but WITHOUT ANY WARRANTY; without even the implied warranty of\n".
         "MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the\n".
         "GNU Affero General Public License 3 for more details.\n".
         "\n".
         "You should have received a copy of the GNU Affero General Public License 3\n".
         "along with Tutorial \"Text-Browsergames mit AJAX und PHP\". If not, see &lt;<a href=\"http://www.gnu.org/licenses/\">http://www.gnu.org/licenses/</a>&gt;.\n".
         "\n".
         "The complete source code is available at &lt;<a href=\"http://www.skreutzer.de/browsergames/technologien/text_browsergames_mit_ajax_und_php/\">http://www.skreutzer.de/browsergames/technologien/text_browsergames_mit_ajax_und_php/</a>&gt;.\n".
         "        </pre>\n";
}
else
{
    require_once("database.inc.php");

    // Benutzer-Eingaben müssen _IMMER_ (!!!) erst bearbeitet
    // werden, bevor sie in ein SQL-Query eingefügt werden dürfen.
    // Es könnten unerlaubte Zeichen enthalten sein mit dem Ziel,
    // die Datenbank zu manipulieren.
    $name = @mysql_real_escape_string($_POST['name']);


    $user = false;

    if ($name != false &&
        $mysql_connection != false)
    {
        $user = mysql_query("SELECT `id`,\n".
                            "    `salt`,\n".
                            "    `password`\n".
                            "FROM `user`\n".
                            "WHERE `name` LIKE '".$name."'\n",
                            $mysql_connection);
    }

    if ($user != false)
    {
        if (mysql_num_rows($user) == 0)
        {
            // Den Benutzer gibt es noch nicht, also anlegen.

            require_once("gamelib.inc.php");

            $user = insertNewUser($name, $_POST['passwort']);

            if ($user > 0)
            {
                $user = array("id" => $user);
            }
            else
            {
                $user = NULL;
            }
        }
        else
        {
            // Den Benutzer gibt es bereits, er will sich
            // wiederholt anmelden.

            $result = mysql_fetch_assoc($user);
            mysql_free_result($user);
            $user = $result;

            if ($user['password'] === hash('sha512', $user['salt'].$_POST['passwort']))
            {
                $user = array("id" => $user['id']);
            }
            else
            {
                // Die Sicherheit kann dadurch erhöht werden, indem dem Benutzer nicht
                // mitgeteilt wird, ob der Benutzername nicht vorhanden oder aber das
                // Passwort falsch war. Dann könnte nicht mehr durch Probieren überprüft
                // werden, welche Benutzernamen bereits vergeben sind.

                echo "        <p>\n".
                     "          Falsches Passwort. <a href=\"index.php\">Nochmal versuchen</a>.\n".
                     "        </p>\n".
                     "    </body>\n".
                     "</html>\n".
                     "\n";

                exit();
            }
        }
    }

    if (is_array($user) === true)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $name;

        echo "        <p>\n".
             "          Die Steuerung des Spiels erfolgt mittels Eingaben über die Tastatur.\n".
             "          Kommandos müssen mit [Enter] bestätigt werden. Um eine Liste der Kommandos\n".
             "          zu erhalten, bitte den Befehl <tt>HILFE</tt> verwenden. Story by JHB. Viel Spaß!\n".
             "        </p>\n".
             "        <div>\n".
             "          Hier <a href=\"game.php\">weiter</a>.\n".
             "        </div>\n";
    }
    else
    {
        echo "        <p>\n".
             "          Problem beim Zugriff auf die Datenbank.\n".
             "        </p>\n";
    }
}

echo "    </body>\n".
     "</html>\n".
     "\n";



?>
