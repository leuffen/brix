<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;

class Chat
{

    public function chat () {
        $ai = BrixEnvFactorySingleton::getInstance()->getEnv()->getOpenAiApi();

        $ai->defineFunction("get_date_time", function () {
            return date("Y-m-d H:i:s");
        });

        $ai->textComplete("What is the date and time?", streamOutput: true);
    }


}
