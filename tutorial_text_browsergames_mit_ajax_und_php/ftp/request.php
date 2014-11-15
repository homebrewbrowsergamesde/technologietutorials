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
 * @file $/request.php
 * @author Stephan Kreutzer
 * @since 2012-06-19
 */



define("_DEBUG", 0);



// Session-Pruefung.

$session = session_start();

if ($session === true)
{
    $session = isset($_SESSION['user_id']);
}

if ($session !== true)
{
    exit();
}

// Pruefung der Eingabe.

if (constant("_DEBUG") != 0)
{
    if (isset($_GET['input']) === true)
    {
        $_POST['input'] = $_GET['input'];
    }
    else
    {
        echo "HTTP-GET-Parameter 'input' fehlt.";
        exit();
    }
}

if (isset($_POST['input']) !== true)
{
    exit();
}

// Eingabe vorbereiten.

$input = mb_strtoupper($_POST['input'], "UTF-8");
$input = htmlspecialchars($input, ENT_COMPAT, "UTF-8");
$output = "";

$inputTokens = explode(' ', $input);

foreach ($inputTokens as $i => $token)
{
    if (strlen($token) == 0)
    {
        unset($inputTokens[$i]);
    }
}

if (count($inputTokens) <= 0)
{
    exit();
}



// Szene ermitteln.

if (isset($_SESSION['scene']) == false)
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
        $_SESSION['scene'] = $result['scene'];
    }
}



if (isset($_SESSION['scene']) === true)
{
    if (file_exists("./befehle/".$_SESSION['scene'].".inc.php") === true)
    {
        require_once("./befehle/".$_SESSION['scene'].".inc.php");

        // Befehl ausfuehren.

        if ($commandRoot->parse($inputTokens, $output) >= 0)
        {

        }
        else
        {
            // Fehler!
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



// Ausgabe fuer AJAX.

header("Content-Type: text/xml");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>".
     "<response><input>".mb_strtoupper($input, "UTF-8")."</input>".
     "<output>".$output."</output></response>";



?>
