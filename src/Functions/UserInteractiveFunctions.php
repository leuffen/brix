<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;

class UserInteractiveFunctions
{

    #[AiFunction(desc: "Ask the user for input. Always use this function to ask for more information.")]
    public function askUserQuestion(#[AiParam("The question to ask")]string $question) : string {
        return readline($question . ": ");
    }




}
