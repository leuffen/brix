<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;

class Chat
{

    public function chat ($q) {
        $ai = BrixEnvFactorySingleton::getInstance()->getEnv()->getOpenAiApi();

        $ai->defineFunction("get_date_time",
            /**
             * Determine the current date and time.
             */
            function () {
            return date("Y-m-d H:i:s");
        });
        $ai->defineFunction("create_repository", function ($name) {
            return "github.com/leuffen/$name";
        });
        $ai->defineFunction("ask_user_question",
            /**
             * The only method to interact with the user. The question is passed as first parameter.
             * The user input is returned.
             */
            function ($question) {
                return readline($question . ": ");
            }
            );

        $ai->defineFunction("list_all_images",
            /**
             * Retrieve list of available images with description.
             */
            function ($repository) {
            return "Image1, Image2, Image3";
        });

        $ai->defineFunction("list_files", function ($path= ".") {
            return glob($path . "/*");
        });
        $ai->textComplete($q, streamOutput: true);
    }


}
