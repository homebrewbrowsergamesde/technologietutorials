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
 * @file $/gamelib.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-18
 */



require_once(dirname(__FILE__)."/database.inc.php");



// Parameter $name muss zur direkten Verwendung in SQL-Anweisung
// vorbereitet worden sein!
function insertNewUser($name, $passwort)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -2;
    }

    $salz = md5(uniqid(rand(), true));
    // Passwörter dürfen _NIE_ (!!!) im Klartext gespeichert
    // werden!
    $passwort = hash('sha512', $salz.$passwort);

    if (mysql_query("INSERT INTO `user` (`id`,\n".
                    "    `name`,\n".
                    "    `salt`,\n".
                    "    `password`,\n".
                    "    `scene`)\n".
                    "VALUES (NULL,\n".
                    "    '".$name."',\n".
                    "    '".$salz."',\n".
                    "    '".$passwort."',\n".
                    "    'start')\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -3;
    }

    $id = mysql_insert_id($mysql_connection);

    if ($id == 0)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -4;
    }


    // Initialisierung der Spieler-Daten.

    if (mysql_query("INSERT INTO `variables_user` (`variable`,\n".
                    "    `user_id`,\n".
                    "    `value`)\n".
                    "VALUES\n".
                    "('status_start', ".$id.", '0')",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -5;
    }

    if (mysql_query("INSERT INTO `inventory` (`user_id`,\n".
                    "    `name`,\n".
                    "    `amount`)\n".
                    "VALUES\n".
                    "(".$id.", 'SPACENTS', 0),\n".
                    "(".$id.", 'BANKKARTE', 1)\n",
                    $mysql_connection) !== true)
    {
        mysql_query("ROLLBACK", $mysql_connection);
        return -6;
    }

    if (mysql_query("COMMIT", $mysql_connection) === true)
    {
        return $id;
    }

    mysql_query("ROLLBACK", $mysql_connection);
    return -7;
}

function setScene($userID, $newScene, &$description)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (mysql_query("UPDATE `user`\n".
                    "SET `scene`='".$newScene."'\n".
                    "WHERE `id`=".$userID."\n",
                    $mysql_connection) !== true)
    {
        return -2;
    }

    $_SESSION['scene'] = $newScene;

    if (file_exists(dirname(__FILE__)."/szenen/".$newScene.".inc.php") === true)
    {
        require_once(dirname(__FILE__)."/szenen/".$newScene.".inc.php");

        if (function_exists("DEFAULTHANDLER_Description") === true &&
            is_callable("DEFAULTHANDLER_Description") === true)
        {
            $description = DEFAULTHANDLER_Description();
        }
        else
        {
            return 2;
        }
    }
    else
    {
        return 1;
    }

    return 0;
}

function getUserVariables($userID, $names)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return NULL;
    }

    if (is_array($names) !== true)
    {
        return NULL;
    }

    $sqlString = "SELECT `variable`,\n".
                 "    `value`\n".
                 "FROM `variables_user`\n".
                 "WHERE (";

    {
        $actualNames = 0;

        foreach ($names as $name)
        {
            if (is_string($name) === true)
            {
                $sqlString .= "`variable` LIKE '".$name."' OR ";
                $actualNames++;
            }
        }

        if ($actualNames < count($names))
        {
            echo "<p>\n".
                 "  Von den Benutzervariablen ";

            foreach ($names as $name)
            {
                echo "<code>".$name."</code> ";
            }

            echo "sind nur ".$actualNames." Variablennamen gültig.\n".
                 "</p>\n";

            return NULL;
        }

        if ($actualNames <= 0)
        {
            echo "<p>\n".
                 "  Keine Benutzervariablen zum Auslesen.\n".
                 "</p>\n";

            return NULL;
        }
    }

    $sqlString = substr($sqlString, 0, -4);
    $sqlString .= ") AND `user_id`=".$userID."\n";

    $variables = mysql_query($sqlString, $mysql_connection);

    if ($variables == false)
    {
        return NULL;
    }

    {
        $result = array();

        while ($temp = mysql_fetch_assoc($variables))
        {
            if (isset($result[$temp['variable']]) == true)
            {
                echo "<p>\n".
                     "  Benutzervariable <code>".$temp['variable']."</code> doppelt in der\n".
                     "  Datenbank vorhanden.\n".
                     "</p>\n";
            }
        
            $result[$temp['variable']] = $temp['value'];
        }

        mysql_free_result($variables);
        $variables = $result;
    }
    

    if (is_array($variables) !== true)
    {
        echo "<p>\n".
              "  Fehlgeschlagen, die Benutzervariablen ";
    
        foreach ($names as $name)
        {
            echo "<code>".$name."</code> ";
        }

        echo "auszulesen.\n".
             "</p>\n";
             
        return NULL;
    }
    
    $missing = array();
    
    foreach ($names as $name)
    {
        if (array_key_exists($name, $variables) == false)
        {
            $missing[] = $name;
        }
    }
    
    if (count($missing) > 0)
    {
        echo "<p>\n".
             "  Aus den Benutzervariablen ";
    
        foreach ($names as $name)
        {
            echo "<code>".$name."</code> ";
        }
        
        echo "konnten ";
        
        foreach ($missing as $name)
        {
            echo "<code>".$name."</code> ";
        }

        echo "nicht ausgelesen werden.\n".
             "</p>\n";

        return NULL;
    }

    return $variables;
}

function setUserVariables($userID, $variables)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (is_array($variables) !== true)
    {
        return -2;
    }


    {
        $actualVariables = 0;

        foreach ($variables as $name => $value)
        {
            if (is_string($name) === true)
            {
                $actualVariables++;
            }
        }

        if ($actualVariables < count($variables))
        {
            echo "<p>\n".
                 "  Von den Benutzervariablen ";

            foreach ($variables as $name => $value)
            {
                echo "<code>".$name."</code> ";
            }

            echo "sind nur ".$actualVariables." Variablennamen gültig.\n".
                 "</p>\n";

            return -3;
        }

        if ($actualVariables <= 0)
        {
            echo "<p>\n".
                 "  Keine Benutzervariablen zum Schreiben.\n".
                 "</p>\n";

            return -4;
        }
    }


    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -5;
    }

    foreach ($variables as $name => $value)
    {
        if (mysql_query("UPDATE `variables_user`\n".
                        "SET `value`='".((string)$value)."'\n".
                        "WHERE `variable` LIKE '".$name."' AND\n".
                        "    `user_id`=".$userID."\n",
                        $mysql_connection) !== true)
        {
            echo "<p>\n".
                 "  Setzen der Benutzervariable <code>".$name."</code> auf <tt>'".$value."'</tt>\n".
                 "  fehlgeschlagen.\n".
                 "</p>\n";

            mysql_query("ROLLBACK", $mysql_connection);
            return -6;
        }
    }

    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -7;
    }

    return 0;
}

function getGlobalVariables($names)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return NULL;
    }

    if (is_array($names) !== true)
    {
        return NULL;
    }

    $sqlString = "SELECT `variable`,\n".
                 "    `value`\n".
                 "FROM `variables_global`\n".
                 "WHERE (";

    {
        $actualNames = 0;

        foreach ($names as $name)
        {
            if (is_string($name) === true)
            {
                $sqlString .= "`variable` LIKE '".$name."' OR ";
                $actualNames++;
            }
        }

        if ($actualNames < count($names))
        {
            echo "<p>\n".
                 "  Von den globalen Variablen ";

            foreach ($names as $name)
            {
                echo "<code>".$name."</code> ";
            }

            echo "sind nur ".$actualNames." Variablennamen gültig.\n".
                 "</p>\n";

            return NULL;
        }

        if ($actualNames <= 0)
        {
            echo "<p>\n".
                 "  Keine globalen Variablen zum Auslesen.\n".
                 "</p>\n";

            return NULL;
        }
    }

    $sqlString = substr($sqlString, 0, -4);
    $sqlString .= ")\n";

    $variables = mysql_query($sqlString, $mysql_connection);

    if ($variables == false)
    {
        return NULL;
    }

    {
        $result = array();

        while ($temp = mysql_fetch_assoc($variables))
        {
            if (isset($result[$temp['variable']]) == true)
            {
                echo "<p>\n".
                     "  Globale Variable <code>".$temp['variable']."</code> doppelt in der\n".
                     "  Datenbank vorhanden.\n".
                     "</p>\n";
            }

            $result[$temp['variable']] = $temp['value'];
        }

        mysql_free_result($variables);
        $variables = $result;
    }
    

    if (is_array($variables) !== true)
    {
        echo "<p>\n".
              "  Fehlgeschlagen, die globalen Variablen ";
    
        foreach ($names as $name)
        {
            echo "<code>".$name."</code> ";
        }

        echo "auszulesen.\n".
             "</p>\n";
             
        return NULL;
    }
    
    $missing = array();
    
    foreach ($names as $name)
    {
        if (array_key_exists($name, $variables) == false)
        {
            $missing[] = $name;
        }
    }
    
    if (count($missing) > 0)
    {
        echo "<p>\n".
              "  Aus den globalen Variablen ";
    
        foreach ($names as $name)
        {
            echo "<code>".$name."</code> ";
        }
        
        echo "konnten ";
        
        foreach ($missing as $name)
        {
            echo "<code>".$name."</code> ";
        }

        echo "nicht ausgelesen werden.\n".
             "</p>\n";

        return NULL;
    }

    return $variables;
}

function setGlobalVariables($variables)
{
    global $mysql_connection;

    if ($mysql_connection == false)
    {
        return -1;
    }

    if (is_array($variables) !== true)
    {
        return -2;
    }


    {
        $actualVariables = 0;

        foreach ($variables as $name => $value)
        {
            if (is_string($name) === true)
            {
                $actualVariables++;
            }
        }

        if ($actualVariables < count($variables))
        {
            echo "<p>\n".
                 "  Von den globalen Variablen ";

            foreach ($variables as $name => $value)
            {
                echo "<code>".$name."</code> ";
            }

            echo "sind nur ".$actualVariables." Variablennamen gültig.\n".
                 "</p>\n";

            return -3;
        }

        if ($actualVariables <= 0)
        {
            echo "<p>\n".
                 "  Keine globalen Variablen zum Schreiben.\n".
                 "</p>\n";

            return -4;
        }
    }


    if (mysql_query("BEGIN", $mysql_connection) !== true)
    {
        return -5;
    }

    foreach ($variables as $name => $value)
    {
        if (mysql_query("UPDATE `variables_global`\n".
                        "SET `value`='".((string)$value)."'\n".
                        "WHERE `variable` LIKE '".$name."'\n",
                        $mysql_connection) !== true)
        {
            echo "<p>\n".
                 "  Setzen der globalen Variable <code>".$name."</code> auf <tt>'".$value."'</tt>\n".
                 "  fehlgeschlagen.\n".
                 "</p>\n";

            mysql_query("ROLLBACK", $mysql_connection);
            return -6;
        }
    }

    if (mysql_query("COMMIT", $mysql_connection) !== true)
    {
        return -7;
    }

    return 0;
}



?>
