<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Business\CreatePageFlow;
use Leuffen\Brix\Type\BrixEnv;
use Leuffen\Brix\Type\T_BrixConfig;
use Phore\FileSystem\PhoreDirectory;

class CliCommands
{

    private BrixEnv $brixEnv;

    private OpenAiApi $openAiApi;

    private string $contextCombined = "";

    public function __construct() {
        $this->brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
        $this->openAiApi = $this->brixEnv->getOpenAiApi();
    }


    public function run_task(string $task_file)
    {
        $flow = new CreatePageFlow($this->brixEnv->brixConfig, $this->brixEnv->rootDir, $this->openAiApi);
        $flow->runTaskFile($task_file);
    }

    public function compact (string $file) {
        $flow = new CreatePageFlow($this->brixEnv->brixConfig, $this->brixEnv->rootDir, $this->openAiApi);
        $flow->compactFile($file);
    }

    public function patch (string $file) {
        $flow = new CreatePageFlow($this->brixEnv->brixConfig, $this->brixEnv->rootDir, $this->openAiApi);
        $flow->patch($file);
    }

    public function patch_file (string $file) {
        $flow = new CreatePageFlow($this->brixEnv->brixConfig, $this->brixEnv->rootDir, $this->openAiApi);
        $flow->patchFile($file);
    }

}
