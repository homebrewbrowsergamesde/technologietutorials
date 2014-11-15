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
 * @file $/commandlib.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-19
 */



class Command
{
    public function __construct($name, $handlerFunctionName)
    {
        $this->name = mb_strtoupper($name, "UTF-8");
        $this->handlerFunctionName = "CMDHANDLER_".$handlerFunctionName;

        $this->subCommands = array();
    }

    public function GetName()
    {
        return $this->name;
    }

    public function AddSubCommand(Command $command)
    {
        $this->subCommands[] = $command;
        return end($this->subCommands);
    }

    public function parse($inputCommands, &$output)
    {
        if (is_array($inputCommands) != true)
        {
            return -1;
        }

        if (count($inputCommands) <= 0)
        {
            return -2;
        }

        $currentCommand = mb_strtoupper($inputCommands[0], "UTF-8");
        array_shift($inputCommands);
        $foundSubCommand = false;

        foreach ($this->subCommands as $subCommand)
        {
            if ($currentCommand == $subCommand->GetName())
            {
                $foundSubCommand = $subCommand;
                break;
            }
        }

        if ($foundSubCommand !== false)
        {
            if (count($inputCommands) > 0)
            {
                // Move one level down (to the sub command of the
                // current object as well as in the input command
                // list).

                return $foundSubCommand->parse($inputCommands, $output);
            }
            else
            {
                // Input command was in the sub command list and
                // the input list ended, so the sub object is the
                // target.

                if ($foundSubCommand->callHandler(array(), $output) !== 0)
                {
                    return -3;
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            // Input command wasn't in the sub command list,
            // so the current object becomes the parent to handle
            // the problem. Maybe it's the target with arguments.

            $furtherCommands = array();
            $furtherCommands[] = $currentCommand;

            foreach ($inputCommands as $inputCommand)
            {
                $furtherCommands[] = $inputCommand;
            }

            if ($this->callHandler($furtherCommands, $output) !== 0)
            {
                return -4;
            }
            else
            {
                return 1;
            }
        }
    }

    public function callHandler($commands, &$output)
    {
        if (function_exists($this->handlerFunctionName) !== true ||
            is_callable($this->handlerFunctionName) !== true)
        {
            return -1;
        }

        $handler = $this->handlerFunctionName;
        $handler($commands, $output);

        return 0;
    }

    private $name;
    private $subCommands;
    private $handlerFunctionName;
}



?>
