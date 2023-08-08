<?php

namespace Leuffen\Brix\Plugins\Website2;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\Frontmatter\Repo\FrontmatterRepoPid;
use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Lack\OpenAi\LackOpenAiResponse;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Plugins\Seo\SeoAnalyzer;
use Phore\Cli\CLIntputHandler;

class Website2CreatorEditor
{

    public function __construct(
        public string $context,
        public FrontmatterRepo $targetRepo,
        public FrontmatterRepo $templateRepo,
        public LackOpenAiClient $client
    ){

    }



    private $modifiedPages = [];

    public function adjust (FrontmatterRepoPid|string $pid, string $lang = null, bool $justMeta = false) {
        $tpl = new JobTemplate(__DIR__ . "/job-adjust.txt");
        $pagePid = $this->targetRepo->selectPid($pid, $lang);
        if ($pagePid->hasTmp()) {
            $page = $pagePid->getTmp();
        } else {
            $page = $pagePid->get();
            $pagePid->setTmp($page);
        }
        if (! $justMeta) {

            $tpl->setData([
                "context" => $this->context,
                "title" => $page->header["title"] ?? "undefined",
                "ai_instructions" => $page->header["_ai_instructions"] ?? ""
            ]);
            $this->client->reset($tpl->getSystemContent(), 0.4);
            $this->client->getCache()->clear();
            $this->client->textComplete([
                $page->body,
                $tpl->getUserContent()
            ], streamer: function (LackOpenAiResponse $response) use ($page) {
                $page->body = $response->getTextCleaned();
                $this->targetRepo->storePage($page);
            });
        }



        $ret = (new SeoAnalyzer($this->client))->analyze($page->body);
        $page->header["description"] = $ret->metaDescription;
      //  $page->header["title"] = $ret->title;
        $page->header["keywords"] = implode(", ", $ret->keywords);
        $this->targetRepo->storePage($page);


        $this->modifiedPages[] = $pagePid;


    }

    
    
    public function adjustMeta() {
        
    }
    

    public function saveAll () {sleep(2);

        foreach ($this->modifiedPages as $page) {
            $page->setTmp(null);
        }
    }



}
