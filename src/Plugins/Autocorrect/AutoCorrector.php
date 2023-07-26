<?php

namespace Leuffen\Brix\Plugins\Autocorrect;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Lack\OpenAi\LackOpenAiResponse;

class AutoCorrector
{

    
    public function __construct(
        public LackOpenAiClient $client
    ){

    }
    


    public function correct (string $file) {
        $tpl = new JobTemplate(__DIR__ . "/job-autocorrect.txt");
        $tpl->setData([
    
        ]);
        
        $file = phore_file($file);
        
        $this->client->reset($tpl->getSystemContent());
        $this->client->getCache()->clear();
        $this->client->textComplete([
            $file->get_contents(),
            $tpl->getUserContent()
        ], streamer: function (LackOpenAiResponse $response) use ($file) {
            $file->set_contents($response->getTextCleaned());
        });
        
    }
    
    
    
}