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
 * @file $/inventorylib.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-09
 */



require_once(dirname(__FILE__)."/database.inc.php");



define("INVENTORY_TALER", 1);
define("INVENTORY_BROETCHEN", 2);
define("INVENTORY_SCHLUESSEL", 3);



/**
 * @class Inventory
 * @brief Ein performanceoptimierender read/write Helper für
 *     Inventar-Manipulation.
 * @details Alle Werte lokal zum Objekt-Scope!
 */
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
            $items = mysql_query("SELECT `type`,\n".
                                 "    `amount`\n".
                                 "FROM `inventory`\n".
                                 "WHERE `user_id`=".$this->userID."\n",
                                 $mysql_connection);

            if ($items != false)
            {
                while ($item = mysql_fetch_assoc($items))
                {
                    $this->inventory[(int)$item['type']] = $item['amount'];
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
                    foreach ($this->modified as $type)
                    {
                        if (mysql_query("UPDATE `inventory`\n".
                                        "SET `amount`=".$this->inventory[$type]."\n".
                                        "WHERE `user_id`=".$this->userID." AND\n".
                                        "    `type`=".$type."\n",
                                        $mysql_connection) !== true)
                        {
                            mysql_query("ROLLBACK", $mysql_connection);

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

    public function GetItem($type, &$amount)
    {
        if (isset($this->inventory[$type]) === true)
        {
            $amount = $this->inventory[$type];
            return true;
        }

        return false;
    }

    public function SetItem($type, $amount)
    {
        $this->inventory[$type] = $amount;

        if (in_array($type, $this->modified) != true)
        {
            $this->modified[] = $type;
        }

        return true;
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
