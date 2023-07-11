<?php

namespace Leuffen\Brix\Plugins\ContextReplace;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;

class ContextReplace
{
    
    public function __construct (private LackOpenAiClient $client) {

    }

    
    public function contextReplace(string $input, string $context) : string {
        $tpl = new JobTemplate(__DIR__ . "/context_replace.prompt.txt");

        $tpl->setData([
            "context" => $context
        ]);

        $this->client->reset($tpl->getSystemContent());

        $result = $this->client->textComplete($input, streamOutput: true)->getTextCleaned();

        return $result;
    }

}