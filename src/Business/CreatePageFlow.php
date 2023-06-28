<?php

namespace Leuffen\Brix\Business;

use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Type\T_BrixConfig;
use Phore\FileSystem\PhoreDirectory;

class CreatePageFlow
{

    public function __construct(
        private T_BrixConfig $config,
        private PhoreDirectory $brixDir,
        private OpenAiApi $openAiApi
    )
    {}


    public function createPage(string $template) {

    }

}
