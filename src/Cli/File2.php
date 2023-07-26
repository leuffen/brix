<?php

namespace Leuffen\Brix\Cli;

use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\ContextFunctions;
use Leuffen\Brix\Functions\FileAccessFunctions;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\PythonFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;
use Leuffen\Brix\Plugins\StructuredData\StucturedDataGenerator;
use Leuffen\Brix\Type\BrixEnv;
use Phore\Cli\Exception\CliException;

class File2
{

    private LackOpenAiClient $client;

    private JobDescription $jobDescription;

    private SingleFileAccessFunctions $singleFileAccessFunctions;

    private BrixEnv $brixEnv;

    public function __construct() {
        $this->brixEnv = $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
    }

  
    public function alter ($argv, string $prompt = "Edit the input.") {
        if (count($argv) !== 1)
            throw new CliException("alter [filename] expects exact 1 parameter");

        
        $infile = phore_file($argv[0])->assertFile();
        $outfile = $infile->withFileExtension("~brix~", strictChecks: false)->asFile();
        $outfile->asFile()->touch();
        
        echo "\nOutfile: $outfile\n";
        
        $tpl = new JobTemplate(__DIR__ . "/file2-alter.txt");
        
        
        $oaiClinet = $this->brixEnv->getOpenAiApi();
        $oaiClinet->getCache()->clear();
        $oaiClinet->reset($tpl->getSystemContent());
        
        $oaiClinet->addClass(new SingleFileAccessFunctions($infile, $outfile));
        $oaiClinet->addClass(new ContextFunctions($this->brixEnv->contextCombined));
        $oaiClinet->addClass(new UserInteractiveFunctions());
        
       
        $oaiClinet->textComplete([
            $infile->get_contents(),
            $tpl->getUserContent() . $prompt
        ], streamOutput: true);
       
    }

    
}
