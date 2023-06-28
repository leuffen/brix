<?php

namespace Leuffen\Brix\Type;

class T_BrixConfig
{

    public function __construct(

        public string $templates_dir,

        public string $output_dir,

        public string $context,

    ) {}


}
