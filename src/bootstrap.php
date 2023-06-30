<?php


namespace Leuffen\Brix;


use Leuffen\Brix\Cli\Chat;
use Leuffen\Brix\Cli\CliCommands;
use Leuffen\Brix\Plugins\Shell;
use Phore\Cli\CliDispatcher;

CliDispatcher::addClass(CliCommands::class);
CliDispatcher::addClass(Chat::class);
CliDispatcher::addClass(Shell::class);



