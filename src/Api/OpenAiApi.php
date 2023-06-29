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

        $definition  = [
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


        foreach ($reflection->getParameters() as $parameter) {
            $definition["parameters"]["properties"][$parameter->getName()] = [
                "type" => "string",
                "description" => ""
            ];
            if ( ! $parameter->isOptional()) {
               $definition["parameters"]["required"][] = $parameter->getName();
            }
        }

        $this->messages["functions"][] = $definition;
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

        //print_r ($this->messages);
        $stream = $api->chat()->createStreamed($this->messages);


        $responseFull = [
            "role" => "assistant",
            "content" => "",
            "function_call" => [
                "name" => "",
                "arguments" => ""
            ]
        ];

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta->toArray();
            if (isset($delta["function_call"])) {
                //echo "<FFF>";
                foreach ($delta["function_call"] as $key => $value) {

                    $responseFull["function_call"][$key] .= $value;
                }
                continue;
            }
            foreach ($delta as $key => $value) {
                if ($key === "role") continue;
                if (isset($responseFull[$key])) {
                    $responseFull[$key] .= $value;
                    continue;
                }
                $responseFull[$key] = $value;
            }
            echo $delta["content"] ?? "";

        }

        //echo "\n" . json_encode($responseFull);;
        $this->messages["messages"][] = $responseFull;
        if ($responseFull["function_call"]["name"] !== "") {
            $functionName = $responseFull["function_call"]["name"];
            $functionArguments = json_decode($responseFull["function_call"]["arguments"], true) ?? [];

            $function = $this->functions[$functionName]["callback"];
            echo "\n> Calling function $functionName with arguments: " . json_encode($functionArguments) . "\n";
            $return = $function(...$functionArguments);
            $this->messages["messages"][] = [
                "content" => json_encode($return),
                "role" => "function",
                "name" => $functionName
            ];
            $this->textComplete(null, $streamOutput);
        }



        return new OpenAiResult($responseFull["content"]);
    }


}
