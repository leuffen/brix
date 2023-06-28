<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Type\T_BrixConfig;
use Phore\FileSystem\PhoreDirectory;

class CliCommands
{

    private T_BrixConfig $brixConfig;

    private PhoreDirectory $rootDir;

    private OpenAiApi $openAiApi;

    public function __construct() {
        // Try to find .brix.yml in the current directory and all parent directories
        $curDir = phore_dir(getcwd());
        while (true) {
            $brixFile = $curDir->withFileName(".brix.yml");
            if ($brixFile->exists()) {
                $this->brixConfig = $brixFile->get_yaml(T_BrixConfig::class);
                break;
            }
            $curDir = $curDir->withParentDir();
            if ((string)$curDir === "/")
                throw new \InvalidArgumentException("Cannot find .brix.yml in current or parent directories.");
        }
        $this->rootDir = $curDir;

        $this->openAiApi = new OpenAiApi($curDir->withFileName("openai-key.txt")->get_contents());
    }


    public function create_page(string $name)
    {
        echo "Hello World";
    }

}
