<?php
/* Copyright (C) 2011-2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Tabellen-Browsergames mit PHP".
 *
 * Tutorial "Tabellen-Browsergames mit PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Tabellen-Browsergames mit PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Tabellen-Browsergames mit PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/defines.inc.php
 * @author Stephan Kreutzer
 * @since 2011-12-07
 */



define("ENUM_GEBAEUDE_BAUERNHOF", "BAUERNHOF");
define("ENUM_GEBAEUDE_HOLZFAELLER", "HOLZFAELLER");
define("ENUM_GEBAEUDE_STEINBRUCH", "STEINBRUCH");
define("ENUM_GEBAEUDE_HANDELSHAUS", "HANDELSHAUS");

define("ENUM_RESSOURCE_NAHRUNG", "FOOD");
define("ENUM_RESSOURCE_HOLZ", "WOOD");
define("ENUM_RESSOURCE_STEIN", "STONE");
define("ENUM_RESSOURCE_KOHLE", "COAL");
define("ENUM_RESSOURCE_EISEN", "IRON");
define("ENUM_RESSOURCE_GOLD", "GOLD");



function translateEnumGebaeudeToDisplayText($gebaeude)
{
    $gebaeude = strtoupper($gebaeude);

    switch ($gebaeude)
    {
    case ENUM_GEBAEUDE_BAUERNHOF:
        return "Bauernhof";
    case ENUM_GEBAEUDE_HOLZFAELLER:
        return "Holzfäller";
    case ENUM_GEBAEUDE_STEINBRUCH:
        return "Steinbruch";
    case ENUM_GEBAEUDE_HANDELSHAUS:
        return "Handelshaus";
    default:
        return "Unbekannt";
    }

    return "Unbekannt";
}

function translateEnumResourceToDisplayText($ressourceArt)
{
    $ressourceArt = strtoupper($ressourceArt);

    switch ($ressourceArt)
    {
    case ENUM_RESSOURCE_NAHRUNG:
        return "Nahrung";
    case ENUM_RESSOURCE_HOLZ:
        return "Holz";
    case ENUM_RESSOURCE_STEIN:
        return "Stein";
    case ENUM_RESSOURCE_KOHLE:
        return "Kohle";
    case ENUM_RESSOURCE_EISEN:
        return "Eisen";
    case ENUM_RESSOURCE_GOLD:
        return "Gold";
    default:
        return "Unbekannt";
    }

    return "Unbekannt";
}



?>
