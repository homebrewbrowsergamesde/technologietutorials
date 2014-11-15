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
 * @file $/inventorylib.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-14
 */



require_once(dirname(__FILE__)."/database.inc.php");



class Inventory
{
    public function __construct($userID)
    {
        $this->userID = $userID;
        $this->inventory = array();
        $this->modified = array();

        global $mysql_connection;

        if ($mysql_connection != false)
        {
            $items = mysql_query("SELECT `name`,\n".
                                 "    `amount`\n".
                                 "FROM `inventory`\n".
                                 "WHERE `user_id`=".$this->userID."\n",
                                 $mysql_connection);

            if ($items != false)
            {
                while ($item = mysql_fetch_assoc($items))
                {
                    $this->inventory[$item['name']] = $item['amount'];
                }

                mysql_free_result($items);
            }
        }
    }

    public function __destruct()
    {
        if (count($this->modified) > 0)
        {
            global $mysql_connection;

            if ($mysql_connection != false)
            {
                if (mysql_query("BEGIN", $mysql_connection) === true)
                {
                    foreach ($this->modified as $name)
                    {
                        if (mysql_query("UPDATE `inventory`\n".
                                        "SET `amount`=".$this->inventory[$name]."\n".
                                        "WHERE `user_id`=".$this->userID." AND\n".
                                        "    `name` LIKE '".$name."'\n",
                                        $mysql_connection) !== true)
                        {
                            mysql_query("ROLLBACK", $mysql_connection);

                            // TODO.
                            echo "<p>\n".
                                 "  Kritischer Fehler beim Schreiben des Inventars!\n".
                                 "</p>\n";

                            return;
                        }
                    }

                    mysql_query("COMMIT", $mysql_connection);
                }
            }
        }
    }

    public function GetItem($name, &$amount)
    {
        $name = mb_strtoupper($name, "UTF-8");

        if (isset($this->inventory[$name]) === true)
        {
            $amount = $this->inventory[$name];
            return true;
        }

        return false;
    }

    public function SetItem($name, $amount)
    {
        $name = mb_strtoupper($name, "UTF-8");

        if (array_key_exists($name, $this->inventory) === true)
        {
            $this->inventory[$name] = $amount;

            if (in_array($name, $this->modified) != true)
            {
                $this->modified[] = $name;
            }
        }
        else
        {
            return false;
        }

        return true;
    }
    
    public function AddItems($items)
    {
        if (is_array($items) != true)
        {
            return -1;
        }

        if (count($items) <= 0)
        {
            return -2;
        }

        $values = "";

        foreach ($items as $name => $amount)
        {
            $name = mb_strtoupper($name, "UTF-8");

            if ($this->SetItem($name, $amount) == false)
            {
                $values .= "(".$this->userID.", '".$name."', ".$amount."),\n";
            }
            else
            {
                // Existiert bereits und wurde gesetzt.
            }
        }

        if (strlen($values) > 0)
        {
            $values = substr($values, 0, -2);

            global $mysql_connection;

            if ($mysql_connection != false)
            {
                if (mysql_query("INSERT INTO `inventory` (`user_id`,\n".
                                "    `name`,\n".
                                "    `amount`)\n".
                                "VALUES ".$values."\n",
                                $mysql_connection) !== true)
                {
                    return -4;
                }
            }
            else
            {
                return -3;
            }
        }

        foreach ($items as $name => $amount)
        {
            $this->inventory[$name] = $amount;
        }

        return 0;
    }

    public function DiscardModifications()
    {
        $this->modified = array();
    }

    protected $userID;
    protected $inventory;
    protected $modified;
}



?>
