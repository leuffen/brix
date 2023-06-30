<?php

namespace Leuffen\Brix\Plugins;

use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\ShellFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;

class Shell
{

    public function chat() {
        $client = BrixEnvFactorySingleton::getInstance()->getEnv()->getOpenAiApi();


        $client->addClass(new ShellFunctions());
        $client->addClass(new UserInteractiveFunctions());
        $client->textComplete("You assist the user operating on the bash. Ask what to do and run the command. ", streamOutput: true);
    }

}
