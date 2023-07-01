<?php

namespace Leuffen\Brix\Type;

class T_BrixConfig
{

    public function __construct(

        public string $templates_dir,

        public string $output_dir,

        /**
         * @var string|null
         */
        public string|null $context = "",

        public string|null $context_file = null,

    ) {}


}
