<?php

namespace Leuffen\Brix\Cli;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\Frontmatter\Repo\FrontmatterRepoPid;
use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Plugins\Content\ContentCreator;
use Leuffen\Brix\Plugins\Seo\SeoAnalyzer;
use Leuffen\Brix\Plugins\Website\WebsiteCreatorEditor;
use Leuffen\Brix\Plugins\Website2\Website2CreatorEditor;
use Leuffen\Brix\Type\BrixEnv;
use Phore\Cli\CLIntputHandler;
use Phore\Cli\Exception\CliException;

class Website2
{



    public FrontmatterRepo $targetRepo;
    public FrontmatterRepo $templateRepo;

    private BrixEnv $brixEnv;

    public function __construct() {
        $this->brixEnv = $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
        $this->targetRepo = new FrontmatterRepo($brixEnv->targetDir);
        $this->templateRepo = new FrontmatterRepo($brixEnv->templateDir);
    }


    public function create(array $argv, string $lang = "de", string $from = null) {
        if (count($argv) !== 1)
            throw new CliException("create [pid] expects exact 1 parameter");

        $pid = $argv[0];

        $targetPage = $this->targetRepo->selectPid($pid, $lang)->create();
        if ($from !== null) {
             $instructions = $this->targetRepo->selectPid($from, $lang);
             if ( ! $instructions->exists())
                 throw new \InvalidArgumentException("Page '$from' does not exist.");
        } else {
            $instructions = $this->templateRepo->selectPid($pid, $lang);
        }

        if ( ! $instructions->exists()) {
            $cli = new CLIntputHandler();
            $title = $cli->askLine("[New site from _default] Enter title for page {$pid} (lang: {$lang}):");
            $aiInstrStr = $cli->askMultiLine("[New site from _default] Enter adjust instructions for page {$pid} (lang: {$lang}):");
            $instructions = $instructions->getDefault();
            $instructions->header["_ai_instructions"] = $aiInstrStr;
            $instructions->header["title"] = $title;
        }

        if ($instructions instanceof FrontmatterRepoPid)
            $instructions = $instructions->get();

        $targetPage->header = $instructions->header;
        $targetPage->body = $instructions->body;
        $targetPage->header["pid"] = $pid;
        $targetPage->header["lang"] = $lang;

        $this->targetRepo->storePage($targetPage);
        sleep (1);
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

    public function adjust (array $argv, string $lang = "de", bool $just_meta = false) {
        if (count($argv) !== 1)
            throw new CliException("modify [pid] expects exact 1 parameter");

        $pidSelector = $argv[0];
        $logic = new Website2CreatorEditor(
            context: $this->brixEnv->contextCombined,
            targetRepo: $this->targetRepo,
            templateRepo: $this->templateRepo,
            client: $this->brixEnv->getOpenAiApi()
        );
        $cli = new CLIntputHandler();

        foreach ($this->targetRepo->list($pidSelector) as $pid) {
            if ($pid->isSystemPid())
                continue;
            if ( ! $pid->hasTmp())
                continue;
            if ( ! $cli->askBool("Temporary version detected for '{$pid}'. Use this as input?", true))
                $pid->setTmp(null);

        }

        foreach ($this->targetRepo->list($pidSelector) as $pid) {
            if ($pid->isSystemPid())
                continue;
            $logic->adjust($pid, $lang, $just_meta);
        }

        sleep(3);
        echo "\n\n";

        if ($cli->askBool("Save page?", true)) {
            // Remove Temp file
            $logic->saveAll();
        }

    }

    
    public function adjustMeta (array $argv, string $lang = "de", bool $just_meta = false) {
        if (count($argv) !== 1)
            throw new CliException("modify [pid] expects exact 1 parameter");

        $pidSelector = $argv[0];
        $logic = new Website2CreatorEditor(
            context: $this->brixEnv->contextCombined,
            targetRepo: $this->targetRepo,
            templateRepo: $this->templateRepo,
            client: $this->brixEnv->getOpenAiApi()
        );
        $cli = new CLIntputHandler();
        
        $contentCreator = new ContentCreator($this->brixEnv->contextCombined, $this->brixEnv->getOpenAiApi());

        
        foreach ($this->targetRepo->list($pidSelector) as $pid) {
            $cli->out("Adjusting meta for: $pid");
            if ($pid->isSystemPid())
                continue;
            assert($pid instanceof FrontmatterRepoPid);
            $page = $pid->get();
            $page->header["description"] = $contentCreator->getMataDescription($page->header["title"], $page->body);
            $cli->out("Description: " . $page->header["description"] . " Len: " . strlen($page->header["description"]));
            $this->targetRepo->storePage($page);
            
        }
    }


}
