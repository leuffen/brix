<?php

namespace Leuffen\Brix\Cli;

use Leuffen\Brix\Business\AbstractBrixCommand;
use Leuffen\Brix\Plugins\Angebot\AngebotCreator;

class Angebot extends AbstractBrixCommand
{


    public function context($argv, string $details = null) {

        $a = new AngebotCreator($this->brixEnv->getOpenAiApi(), $this->brixEnv->rootDir, $this->brixEnv->getState("angebot"));

        $a->extractDataPrompt($details);

    }

    public function create($argv, string $details = null) {

        $a = new AngebotCreator($this->brixEnv->getOpenAiApi(), $this->brixEnv->rootDir, $this->brixEnv->getState("angebot"));




        $a->create($details);

    }

}
