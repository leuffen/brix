<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Phore\System\PhoreExecException;

class ShellFunctions
{

    #[AiFunction(desc: "Execute a shell command in unix shell and return the output")]
    public function shell_exec(#[AiParam("The unix shell command to run")]string $cmd) : string {
        try {
            return phore_exec($cmd);
        } catch (PhoreExecException $e) {
            return  $e->getMessage();
        }

    }

}
