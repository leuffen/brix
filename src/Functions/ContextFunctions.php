<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;

class ContextFunctions
{

    public function __construct(private string $context)
    {

    }

    #[AiFunction("Load Context for text creation. Load only if explicitly needed or asked for.")]
    public function getContext(
        
    ){
        // Encode the address to be URL-friendly
        return $this->context;
    }

    #[AiFunction("Load the current Date including the current weekday. Load only if needed.")]
    public function getDate(
        
    ){
        // Encode the address to be URL-friendly
        return date("l, Y-m-d");
    }
    
}
