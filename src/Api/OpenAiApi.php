<?php

namespace Leuffen\Brix\Api;
use Orhanerday\OpenAi\OpenAi;

class OpenAiApi
{


    public function __construct(private string $apiKey) {

    }

    public function getApiKey() {
        return trim($this->apiKey);
    }


    public function textComplete($question, bool $streamOutput = false) : OpenAiResult
    {
        $api = \OpenAI::client($this->getApiKey());

        $stream = $api->chat()->createStreamed([
            "model" => "gpt-3.5-turbo-16k",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $question
                ],

            ]
        ]);

        $data = "";

        foreach ($stream as $response) {
            //print_r($response);
            $chars = $response->choices[0]->delta->content;
            echo $chars;
            $data .= $chars;
        }

        return new OpenAiResult($data);
    }


}
