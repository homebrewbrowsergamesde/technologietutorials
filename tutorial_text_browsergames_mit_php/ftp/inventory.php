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
 * @file $/inventory.php
 * @details Separate Seite abseits aller Szenen (übergeordnetes Menü).
 * @author Stephan Kreutzer
 * @since 2012-06-02
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



echo "        <div>\n";

require_once(dirname(__FILE__)."/inventorylib.inc.php");

$inventory = new Inventory($_SESSION['user_id']);

echo "          <form action=\"game.php\" method=\"post\">\n";

$taler = 0;
$inventory->GetItem(INVENTORY_TALER, $taler);
echo "            ".$taler." Taler.<br />\n";

$broetchen = 0;
$inventory->GetItem(INVENTORY_BROETCHEN, $broetchen);

if ($broetchen > 0)
{
    echo "            <input type=\"radio\" name=\"inventory_item\" value=\"".INVENTORY_BROETCHEN."\" />".$broetchen." Brötchen.<br />\n";
}

$schluessel = 0;
$inventory->GetItem(INVENTORY_SCHLUESSEL, $schluessel);

if ($schluessel > 0)
{
    echo "            <input type=\"radio\" name=\"inventory_item\" value=\"".INVENTORY_SCHLUESSEL."\" />".$schluessel." wuchtiger, einst prächtiger alter Schlüssel.<br />\n";
}

echo "            <input type=\"submit\" value=\"Benutzen\" /><br />\n".
     "          </form>\n".
     "        </div>\n".
     "        <div>\n".
     "          <hr />\n".
     "          <a href=\"game.php\">Verlassen</a>.\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n".
     "\n";



?>
