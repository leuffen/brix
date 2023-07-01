<?php

namespace Leuffen\Brix\Plugins\StructuredData;

use Lack\OpenAi\LackOpenAiClient;
use Phore\FileSystem\PhoreFile;

class StucturedDataGenerator
{

    public function __construct(private LackOpenAiClient $client)
    {
    }


    public function fillFile(string|PhoreFile $file, string $contextData) {
        $extention = $file->getExtension();
        $defFile = $file->withDirName()->withFileName($file->getBasename() . ".d.ts");
        if ( ! $defFile->exists()) {
            throw new \InvalidArgumentException("Cannot find definition file for '$defFile'");
        }

        $prompt = <<<EOT
Typedef:

"""
{$defFile->get_contents()}
"""

Context:

"""
$contextData
"""

Extract information from Context and return it as valid $extention format from type fileType. Return contents of exported type fileType.

EOT;
        echo $prompt;
        $ret = $this->client->textComplete($prompt, streamOutput: true);

        switch($extention) {
            case "json":
                $file->set_json(phore_json_decode($ret));
                break;
            case "yaml":
            case "yml":
                $file->set_yaml(phore_yaml_decode($ret));
                break;

            default: throw new \InvalidArgumentException("Cannot handle file extension '$extention'");
        }

    }

}
