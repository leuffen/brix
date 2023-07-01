<?php


namespace Leuffen\Brix;


use Leuffen\Brix\Cli\Chat;
use Leuffen\Brix\Cli\CliCommands;
use Leuffen\Brix\Cli\File;
use Leuffen\Brix\Plugins\Shell;
use Phore\Cli\CliDispatcher;

CliDispatcher::addClass(File::class);
CliDispatcher::addClass(Chat::class);
CliDispatcher::addClass(Shell::class);



