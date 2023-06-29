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

    public $functions = [];

    public $messages = [
        "model" => "gpt-3.5-turbo-16k",
        "messages" => [],
        "functions" => []
    ];


    public function setSystemMessage($message) {
        $this->messages["messages"][] = [
            "content" => $message,
            "role" => "system"
        ];
    }

    public function defineFunction ($name, $callback) {
        $reflection = new \ReflectionFunction($callback);

        $this->messages["functions"][] = [
            "name" => $name,
            "description" => (string)$reflection->getDocComment(),
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "location" => [
                        "type" => "string",
                        "description" => "The location of the user"
                    ],
                ],
                "required" => [],
            ],

        ];

        $this->functions[$name] = [
            "callback" => $callback,
        ];
    }







    public function textComplete($question=null, bool $streamOutput = false) : OpenAiResult
    {
        $api = \OpenAI::client($this->getApiKey());

        if ($question) {
            $this->messages["messages"][] = [
                "content" => $question,
                "role" => "user"
            ];
        }

        $stream = $api->chat()->createStreamed($this->messages);


        $responseFull = [
            "role" => null,
            "content" => "",
            "function_call" => [
                "name" => null,
                "arguments" => []
            ]
        ];

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta->toArray();
            if (isset($delta["function_call"])) {
                foreach ($delta["function_call"] as $key => $value) {
                    $responseFull["function_call"][$key] = $value;
                }
                continue;
            }
            foreach ($delta as $key => $value) {
                if (isset($responseFull[$key])) {
                    $responseFull[$key] .= $value;
                    continue;
                }
                $responseFull[$key] = $value;
            }
            echo $delta["content"] ?? "";

        }


        if ($responseFull["function_call"]["name"] !== null) {
            $functionName = $responseFull["function_call"]["name"];
            $functionArguments = json_decode($responseFull["function_call"]["arguments"]);
            $function = $this->functions[$functionName]["callback"];
            echo "\n> Calling function $functionName with arguments: \n";
            $return = $function($functionArguments);
            $this->messages["messages"][] = [
                "content" => $return,
                "role" => "function",
                "name" => $functionName
            ];
            $this->textComplete(null, $streamOutput);
        } else {
            $this->messages["messages"][] = [
                "content" => $responseFull["content"],
                "role" => "assistant"
            ];
        }

        return new OpenAiResult($responseFull["content"]);
    }


}
