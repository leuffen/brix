<?php

namespace Leuffen\Brix\Plugins\Website2;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Lack\OpenAi\LackOpenAiResponse;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;

class Website2CreatorEditor
{

    public function __construct(
        public string $context,
        public FrontmatterRepo $targetRepo,
        public FrontmatterRepo $templateRepo,
        public LackOpenAiClient $client
    ){

    }



    public function createPage($pid, $lang) {
        $targetPage = $this->targetRepo->selectPid($pid, $lang)->create();
        $instructions = $this->templateRepo->selectPid($pid, $lang)->get();

        $targetPage->body = $instructions->body;
        $targetPage->header = $instructions->header;
        
        $this->targetRepo->storePage($targetPage);
    }


    public function adjust ($pid, $lang) {
        $tpl = new JobTemplate(__DIR__ . "/job-adjust.txt");
        $tpl->setData([
            "context" => $this->context,
        ]);
        $page = $this->targetRepo->selectPid($pid, $lang)->get();
        $this->client->reset($tpl->getSystemContent());
        $this->client->textComplete([
            $page->body,
            $tpl->getUserContent()
        ], streamer: function (LackOpenAiResponse $response) use ($page) {
            $page->body = $response->getTextCleaned();
            $this->targetRepo->storePage($page);
        });
    }
    
  

}
