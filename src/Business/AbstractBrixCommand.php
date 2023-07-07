<?php

namespace Leuffen\Brix\Business;

use Leuffen\Brix\Type\BrixEnv;

class AbstractBrixCommand
{

    protected BrixEnv $brixEnv;

    public function __construct() {
        $this->brixEnv = $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
    }
}
