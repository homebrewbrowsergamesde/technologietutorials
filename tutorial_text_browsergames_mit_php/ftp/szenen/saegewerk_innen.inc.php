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
 * @file $/szenen/saegewerk_innen.inc.php
 * @author Stephan Kreutzer
 * @since 2012-04-11
 */



require_once(dirname(__FILE__)."/../gamelib.inc.php");



if (isset($_POST['wahl']) != true)
{
    echo "<p>\n".
         "  Im Sägewerk gibt es einen zweiten Stock, der in der Mitte des Raumes\n".
         "  eine große Aussparung hat. Vermutlich wird dort Holz mit einem Kran\n".
         "  zur Lagerung hochgehievt. Überall sind Bretter und Baumstämme verteilt.\n".
         "  Im hinteren Teil des Raumes befindet sich eine massive Holzkonstruktion,\n".
         "  welche offenbar der Fixierung der Säge im Innern dient. Ein Mann steht\n".
         "  an der Säge, während ein anderer aufmerksam Baumstämme begutachtet.\n".
         "</p>\n".
         generateHTMLChooseForm("wahl",
                                array("mann_saege" => "Mit dem Mann an der Säge sprechen.",
                                      "mann_begutachter" => "Mit dem anderen Mann sprechen.",
                                      "verlassen" => "Sägewerk verlassen."));
}
else
{
    switch($_POST['wahl'])
    {
    case "mann_saege":
        setScene($_SESSION['user_id'], "saegewerk_innen_mann_saege");
        break;

    case "mann_begutachter":
        setScene($_SESSION['user_id'], "saegewerk_innen_mann_begutachter");
        break;

    case "verlassen":
        if (setScene($_SESSION['user_id'], "saegewerk") === 0)
        {
            echo "<p>\n".
                 "  Du trittst hinaus in das gleißende Licht. Die Luft erscheint besonders\n".
                 "  frisch und angenehm.\n".
                 "</p>\n";
        }

        break;
    }

    unset($_POST['wahl']);
}



?>
