<?php

namespace Leuffen\Brix\Plugins\RechtschreibungCorrect;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;

class RechtschreibungCorrect
{
    
    public function __construct (private LackOpenAiClient $client) {

    }

    
    public function correct(string $input) : string {
        $tpl = new JobTemplate(__DIR__ . "/correct.prompt.txt");

        $this->client->reset($tpl->getSystemContent());

        $result = $this->client->textComplete($input, streamOutput: true)->getTextCleaned();

        return $result;
    }

}