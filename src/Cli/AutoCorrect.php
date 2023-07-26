<?php

namespace Leuffen\Brix\Cli;

use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\AbstractBrixCommand;
use Leuffen\Brix\Plugins\Angebot\AngebotCreator;
use Leuffen\Brix\Plugins\Autocorrect\AutoCorrector;

class AutoCorrect extends AbstractBrixCommand
{


    public function correct($argv) {

        $a = new AutoCorrector($this->brixEnv->getOpenAiApi());

        $a->correct($argv[0]);

    }
    

}
