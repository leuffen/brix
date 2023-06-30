<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\FileAccessFunctions;
use Leuffen\Brix\Functions\ShellFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;

class Chat
{

    public function chat ($argv) {
        $client = BrixEnvFactorySingleton::getInstance()->getEnv()->getOpenAiApi();

        print_r ($argv);

        $client->reset("Use only defined functions. Use functions to interact with user. Ask if you don't understand the task or options are unclear.");
        $client->addClass(new ShellFunctions());
        $client->addClass(new UserInteractiveFunctions());
        $client->addClass(new FileAccessFunctions(getcwd()));
        $client->textComplete(implode(" ", $argv), streamOutput: true);
    }


}
