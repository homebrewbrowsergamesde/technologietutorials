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
 * @file $/game.php
 * @brief Hauptseite, die die jeweilige Szene lädt oder wechselt.
 * @author Stephan Kreutzer
 * @since 2012-04-17
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



$szene = false;

if (isset($_SESSION['scene']) === true)
{
    $szene = $_SESSION['scene'];
}
else
{
    require_once("database.inc.php");

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
        $szene = $result['scene'];
        $_SESSION['scene'] = $szene;
    }
}

if (is_string($szene) !== true)
{
    $szene = "start";
    $_SESSION['scene'] = $szene;
}


//echo "this is: ".$szene."<hr>";

echo "        <div>\n".
     "          <a href=\"inventory.php\">Inventar</a>.\n".
     "          <hr />\n".
     "        </div>\n".
     "        <div>\n";

if (file_exists("./szenen/".$szene.".inc.php") === true)
{
    require_once("./szenen/".$szene.".inc.php");

    if ($szene !== $_SESSION['scene'])
    {
        // Szenenwechsel im Rahmen des require_once() oben
        // aufgetreten. Evtl. wurde eine Wechsel-Nachricht
        // ausgegeben; darauf folgt nun die neue Szene.

        $szene = $_SESSION['scene'];

        if (file_exists("./szenen/".$szene.".inc.php") === true)
        {
            require_once("./szenen/".$szene.".inc.php");
        }
        else
        {
            echo "          <p>\n".
                 "            Fehler im Spiel: Szene <code>".$szene."</code> existiert nicht.\n".
                 "          </p>\n";
        }
    }
}
else
{
    echo "          <p>\n".
         "            Fehler im Spiel: Szene <code>".$szene."</code> existiert nicht.\n".
         "          </p>\n";
}

echo "        </div>\n".
     "        <div>\n".
     "          <hr />\n".
     "          <a href=\"logout.php\">Beenden</a>.\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n".
     "\n";



?>
