<?php

namespace Leuffen\Brix\Plugins\ExtractUserData;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;

class ExtractUserData
{

    public function __construct(private LackOpenAiClient $client)
    {
    }

    /**
     * @template T
     * @param string $inputData
     * @param class-string<T> $classType
     * @return T
     * @throws \ReflectionException
     */
    public function extractUserData(string $inputData, string $classType) : object {
        $reflection = new \ReflectionClass($classType);
        $tpl = new JobTemplate(__DIR__ . "/prompt.txt");
        $tpl->setData(
            [
                "structure" => file_get_contents($reflection->getFileName()),
                "input" =>  $inputData
                
            ]
        );
        return $this->client->textComplete($tpl->getSystemContent(), streamOutput: true)->getJson($classType);
    }
    
}