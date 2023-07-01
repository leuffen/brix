<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;

class PythonFunctions
{

    #[AiFunction("Call python")]
    public function python (#[AiParam("Parameters to add to python executable")]$params = null) {
        print_r ($params);
        return "Python 8.3.4";
    }

}
