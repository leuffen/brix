<?php


namespace Leuffen\Brix;


use Leuffen\Brix\Cli\Angebot;
use Leuffen\Brix\Cli\AutoCorrect;
use Leuffen\Brix\Cli\Chat;
use Leuffen\Brix\Cli\CliCommands;
use Leuffen\Brix\Cli\File;
use Leuffen\Brix\Cli\File2;
use Leuffen\Brix\Cli\Website;
use Leuffen\Brix\Cli\Website2;
use Leuffen\Brix\Plugins\Shell;
use Phore\Cli\CliDispatcher;

CliDispatcher::addClass(File::class);
CliDispatcher::addClass(Chat::class);
CliDispatcher::addClass(Shell::class);
CliDispatcher::addClass(Website::class);
CliDispatcher::addClass(Angebot::class);
CliDispatcher::addClass(Website2::class);

CliDispatcher::addClass(File2::class);

CliDispatcher::addClass(AutoCorrect::class);




