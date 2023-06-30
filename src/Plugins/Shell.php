<?php

namespace Leuffen\Brix\Plugins;

use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\FileAccessFunctions;
use Leuffen\Brix\Functions\ShellFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;

class Shell
{

    public function chat(array $argv) {
        $client = BrixEnvFactorySingleton::getInstance()->getEnv()->getOpenAiApi();

        print_r($argv);

        $client->addClass(new ShellFunctions());
        $client->addClass(new UserInteractiveFunctions());
        $client->addClass(new FileAccessFunctions(getcwd()));
        $client->textComplete("You assist the user operating on the bash. Ask what to do and run the command. ", streamOutput: true);
    }

}
