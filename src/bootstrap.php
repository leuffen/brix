<?php


namespace Leuffen\Brix;


use Leuffen\Brix\Cli\CliCommands;
use Phore\Cli\CliDispatcher;

CliDispatcher::addClass(CliCommands::class);

