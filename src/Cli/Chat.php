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
        $ai->defineFunction("exec_shell_command",
            /**
             * Run a command on the bash shell of the linux operating system. Returns the output.
             */
            function ($command) {
                try {
                    return phore_exec($command);
                } catch (\Exception $e) {
                    return "Error: " . $e->getMessage();
                }
        });
        $ai->defineFunction("list_files", function ($path= ".") {
            return glob($path . "/*");
        });
        $ai->textComplete($q, streamOutput: true);
    }


}
