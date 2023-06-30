<?php

namespace Leuffen\Brix\Cli;

use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\FileAccessFunctions;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\ShellFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;

class Chat
{


    private LackOpenAiClient $client;

    public function __construct() {
        $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
        $this->client = $client = $brixEnv->getOpenAiApi();

        $jobDescription = new JobDescription();
        $jobDescription->addContext($brixEnv->contextCombined);
        $jobDescription->addRule("Use only defined functions.");
        $jobDescription->addRule("Search information from context provided.");
        $jobDescription->addRule("If you don't know the answer inert [data-missing].");
        $jobDescription->addRule("Always preserve structure, whitespace and comments when interacting with yaml / json.");
        $jobDescription->addRule("Follow instructions provided as comments in yaml / json files.");
        $jobDescription->addRule("Ask if you don't understand the task or options are unclear.");

        $client->reset($jobDescription);
        $client->addClass(new UserInteractiveFunctions());
        $client->addClass(new GoogleMapsFunctions($brixEnv->keyStore->getAccessKey(Service::GoogleMaps)));
        $client->addClass(new FileAccessFunctions(getcwd()));
    }

    public function chat ($argv, bool $readonly = false) {

        if (count($argv) > 0)
            $this->client->textComplete(implode(" ", $argv), streamOutput: true);

        while (true) {
            echo "\nOutput ended\n";
            $input = readline("Input: ");
            if (trim ($input) === "")
                return;
            $this->client->textComplete($input, streamOutput: true);
        }
    }


    public function plan($argv) {
         if (count($argv) > 0)
            $this->client->textComplete(implode(" ", $argv) . ". Do not run api calls (exept ask). Just plan. Develop a plan to fulfill this task. Keep it simple.", streamOutput: true);

        while (true) {
            echo "\nOutput ended\n";
            $input = readline("Input: ");
            if (trim ($input) === "")
                return;
            $this->client->textComplete($input, streamOutput: true);
        }
    }


}
