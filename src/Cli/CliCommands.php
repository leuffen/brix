<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\CreatePageFlow;
use Leuffen\Brix\Type\T_BrixConfig;
use Phore\FileSystem\PhoreDirectory;

class CliCommands
{

    private T_BrixConfig $brixConfig;

    private PhoreDirectory $rootDir;

    private OpenAiApi $openAiApi;

    private string $contextCombined = "";

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

        $this->contextCombined .= $this->brixConfig->context ?? "";
        if ($this->brixConfig->context_file !== null) {
            $this->contextCombined .= "\n" . phore_file($this->brixConfig->context_file)->get_contents();
        }

        $this->openAiApi = new OpenAiApi($curDir->withFileName("openai-key.txt")->get_contents());
    }


    public function run_task(string $task_file)
    {
        $flow = new CreatePageFlow($this->brixConfig, $this->rootDir, $this->openAiApi);
        $flow->runTaskFile($task_file);
    }

    public function compact (string $file) {
        $flow = new CreatePageFlow($this->brixConfig, $this->rootDir, $this->openAiApi);
        $flow->compactFile($file);
    }

    public function patch (string $file) {
        $flow = new CreatePageFlow($this->brixConfig, $this->rootDir, $this->openAiApi, $this->contextCombined);
        $flow->patch($file);
    }

    public function patch_file (string $file) {
        $flow = new CreatePageFlow($this->brixConfig, $this->rootDir, $this->openAiApi, $this->contextCombined);
        $flow->patchFile($file);
    }

}
