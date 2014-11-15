<?php
/* Copyright (C) 2012  Stephan Kreutzer
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
 * @file $/game.php
 * @author Stephan Kreutzer
 * @since 2012-06-16
 */



$session = session_start();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
     "    <head>\n".
     "        <title>Textgame</title>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\" />\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\" />\n".
     "        <script src=\"ttyajax.js\" type=\"text/javascript\"></script>\n".
     "        <style type=\"text/css\">\n".
     "          input { font-family: monospace; }\n".
     "          p { font-family: monospace; }\n".
     "        </style>\n".
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



// Szene immer ausgeben, sowohl beim Starten als auch beim
// Aktualisieren.

if (isset($_SESSION['scene']) == false)
{
    require_once("database.inc.php");

    $szene = false;

    if ($mysql_connection != false)
    {
        $szene = mysql_query("SELECT `scene`".
                             "FROM `user`\n".
                             "WHERE `id`=".$_SESSION['user_id']."\n",
                             $mysql_connection);
    }

    if ($szene != false)
    {
        $result = mysql_fetch_assoc($szene);
        mysql_free_result($szene);
        $_SESSION['scene'] = $result['scene'];
    }
}

$description = NULL;

if (isset($_SESSION['scene']) === true)
{
    if (file_exists("./szenen/".$_SESSION['scene'].".inc.php") === true)
    {
        require_once("./szenen/".$_SESSION['scene'].".inc.php");

        if (function_exists("DEFAULTHANDLER_Description") === true &&
            is_callable("DEFAULTHANDLER_Description") === true)
        {
            $description = DEFAULTHANDLER_Description();
        }
        else
        {

        }
    }
    else
    {
          //   "            Fehler im Spiel: Szene <code>".$szene."</code> existiert nicht.\n".
    }
}
else
{

}



echo "        <div id=\"output\">\n";

if (is_string($description) === true)
{
    echo "          <p>\n".
         "            ".$description."\n".
         "          </p>\n";
}

echo "        </div>\n".
     "        <div>\n".
     "          <form action=\"\" method=\"\" onsubmit=\"return ttyHandler();\" >\n".
     "            <fieldset id=\"form\">\n".
     "              <input type=\"text\" size=\"80\" name=\"tty\" id=\"tty\" />\n".
     "            </fieldset>\n".
     "          </form>\n".
     "        </div>\n";



echo "    </body>\n".
     "</html>\n".
     "\n";



?>
