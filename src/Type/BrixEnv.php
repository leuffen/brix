<?php

namespace Leuffen\Brix\Type;

use Lack\OpenAi\LackOpenAiClient;
use Lack\OpenAi\Logger\CliLogger;
use Leuffen\Brix\Api\OpenAiApi;
use Phore\FileSystem\PhoreDirectory;

class BrixEnv
{

    public function __construct(
        private string $openAiApiKey,
        public readonly T_BrixConfig $brixConfig,
        public readonly PhoreDirectory $rootDir,
        public readonly PhoreDirectory $targetDir,
        public readonly PhoreDirectory $templateDir,
        public readonly string $contextCombined
    ) {

    }


    public function getOpenAiApi() : LackOpenAiClient {
        return new LackOpenAiClient($this->openAiApiKey, new CliLogger());
    }



}
