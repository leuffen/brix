<?php

namespace Leuffen\Brix\Cli;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Plugins\Content\ContentCreator;
use Leuffen\Brix\Plugins\Seo\SeoAnalyzer;
use Leuffen\Brix\Plugins\Website\WebsiteCreatorEditor;
use Leuffen\Brix\Type\BrixEnv;
use Phore\Cli\Exception\CliException;

class Website
{



    public FrontmatterRepo $targetRepo;
    public FrontmatterRepo $templateRepo;

    private BrixEnv $brixEnv;

    public function __construct() {
        $this->brixEnv = $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
        $this->targetRepo = new FrontmatterRepo($brixEnv->targetDir);
        $this->templateRepo = new FrontmatterRepo($brixEnv->templateDir);
    }


    public function create(array $argv, string $lang = "de") {
        if (count($argv) !== 1)
            throw new CliException("create [pid] expects exact 1 parameter");

        $pid = $argv[0];
        
        $targetPage = $this->targetRepo->selectPid($pid, $lang)->create();
        $instructions = $this->templateRepo->selectPid($pid, $lang)->get(true);

        $targetPage->header = $instructions->header;
        $targetPage->body = $instructions->body;
        $targetPage->header["pid"] = $pid;
        $targetPage->header["lang"] = $lang;

        $this->targetRepo->storePage($targetPage);
        
        echo "Created page: $pid ($lang)\n";
    }

    public function list(array $argv, string $lang = "de") {
        $filter = $argv[0] ?? "*";
        echo "\nList availabe template pid:\n";
        $pages = $this->templateRepo->list($filter, $lang);
        foreach ($pages as $page) {
            echo ">" . $page . "\n";
        }
    }

    public function modify (array $argv, string $lang = "de") {
        if (count($argv) !== 1)
            throw new CliException("modify [pid] expects exact 1 parameter");

        $pid = $argv[0];

        $logic = new WebsiteCreatorEditor(
            context: $this->brixEnv->contextCombined,
            targetRepo: $this->targetRepo,
            templateRepo: $this->templateRepo,
            client: $this->brixEnv->getOpenAiApi()
        );
        $logic->editPage($pid, $lang);

    }
    
    
    public function suggest(array $argv, string $lang = "de") {
        if (count($argv) !== 1)
            throw new CliException("suggest [pid] expects exact 1 parameter");

        $subject = implode(" ", $argv) ; 

        $logic = new ContentCreator($this->brixEnv->contextCombined, $this->brixEnv->getOpenAiApi());
        
        $logic->suggestTextStructure($subject);

    }

    public function brainstorm(array $argv, string $lang = "de") {
        if (count($argv) !== 1)
            throw new CliException("suggest [pid] expects exact 1 parameter");

        $subject = implode(" ", $argv) ; 

        $logic = new ContentCreator($this->brixEnv->contextCombined, $this->brixEnv->getOpenAiApi());
        
        $logic->brainstormText($subject);

    }
    
    public function ai_suggest(array $argv) {
        if (count($argv) !== 1)
            throw new CliException("ai_suggest [pid] expects exact 1 parameter");

        $pid = $argv[0];

        $logic = new ContentCreator(
            context: $this->brixEnv->contextCombined,
            client: $this->brixEnv->getOpenAiApi()
        );
        
        foreach ($this->targetRepo->list($pid) as $pagePid) {
            echo "\nAI Suggesting for: $pagePid";
            $page = $pagePid->get();
            if ( ! $logic->runForPage($page))
                echo "SKIP";
            
            $this->targetRepo->storePage($page);
        }
        

    }

}
