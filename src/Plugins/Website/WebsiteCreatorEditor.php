<?php

namespace Leuffen\Brix\Plugins\Website;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;

class WebsiteCreatorEditor
{

    public function __construct(
        public string $context,
        public FrontmatterRepo $targetRepo,
        public FrontmatterRepo $templateRepo,
        public LackOpenAiClient $client
    ){

    }



    public function createPage($pid, $lang) {

        $aiTpl = new JobTemplate(__DIR__ . "/job-create.txt");
        $aiTpl->setData([
            "pid" => $pid,
            "lang" => $lang,
            "context" => $this->context,
            // Maybe also copy templates to target? So they can be extended?
            "templates" => $this->templateRepo->selectPid($pid, $lang)->getElementsDef(),
            "instructions" => $this->templateRepo->selectPid($pid, $lang)->get()->body,
        ]);

        $targetPage = $this->targetRepo->selectPid($pid, $lang)->create();
        $instructions = $this->templateRepo->selectPid($pid, $lang)->get();

        $this->client->reset($aiTpl->getSystemContent());

        $targetPage->header = $instructions->header;
        $targetPage->body = $this->client->textComplete($aiTpl->getUserContent(), streamOutput: true)->getTextCleaned();
        $this->targetRepo->storePage($targetPage);
    }


    public function editPage($pid, $lang)
    {

        $aiTpl = new JobTemplate(__DIR__ . "/job-update.txt");
        $aiTpl->setData([
            "pid" => $pid,
            "lang" => $lang,
            "context" => $this->context,
            // Maybe also copy templates to target? So they can be extended?
            "templates" => $this->templateRepo->selectPid($pid, $lang)->getElementsDef(),
            "content" => $this->targetRepo->selectPid($pid, $lang)->get()->body,
        ]);

        $targetPage = $this->targetRepo->selectPid($pid, $lang)->get();

        $this->client->reset($aiTpl->getSystemContent());


        $this->client->addClass(
            new SingleFileAccessFunctions(
                fn() => $this->targetRepo->selectPid($pid, $lang)->get()->body,
                function (string $content) use ($targetPage) {
                    $targetPage->body = $content;
                    $this->targetRepo->storePage($targetPage);
                }
            )
        );
        $this->client->interactive($aiTpl->getUserContent());
    }

    public function editPageIncremental($pid, $lang)
    {

        $aiTpl = new JobTemplate(__DIR__ . "/job-update-incrmental.txt");
        $aiTpl->setData([
            "pid" => $pid,
            "lang" => $lang,
            "context" => $this->context,
            // Maybe also copy templates to target? So they can be extended?
            "templates" => $this->templateRepo->selectPid($pid, $lang)->getElementsDef(),
            "content" => $this->targetRepo->selectPid($pid, $lang)->get()->body,
        ]);

        $targetPage = $this->targetRepo->selectPid($pid, $lang)->get();

        $this->client->reset($aiTpl->getSystemContent());

        $chunks = str_split($targetPage->body, 100);
        $this->client->addFunction(
            #[AiFunction("load original content in chunks (string[]). Use saveChunk(index, data) to save changed chunks. Returns json-encoded array of chunks.", "loadChunks")]
            function () use ($chunks) {
                $data = phore_json_encode($chunks);
                return $data;
            }
        );
        $this->client->addFunction(
            #[AiFunction("save chunks. Replace all unmodified chunks by null before sending!", "saveChunks")]
            function (#[AiParam("json encoded array of chunks. Set unmodified chunks to null to safe bandwidth")]string $chunksJson) use ($targetPage, &$chunks) {
                $newChunks = phore_json_decode($chunksJson);
                print_r ($newChunks);
                for ($i = 0; $i< count($newChunks); $i++) {
                    if ($newChunks[$i] === null)
                        continue;
                    $chunks[$i] = $newChunks[$i];
                }
                $targetPage->body = implode("", $chunks);
                $this->targetRepo->storePage($targetPage);
            }
            /*#[AiFunction("save chunks. Call multiple times for multiple changed chunks.", "saveChunk")]
            function (#[AiParam("The numeric index of the modified chunk")]string $chunkIndex, #[AiParam("The new data to save for this chunk")]string $modifiedData) use ($targetPage, $chunks) {
                $chunks[$chunkIndex] = $modifiedData;
                $targetPage->body = implode("", $chunks);
                $this->targetRepo->storePage($targetPage);
            }*/
        );
        /*
        $this->client->addClass(
            new SingleFileAccessFunctions(
                fn() => $this->targetRepo->selectPid($pid, $lang)->get()->body,
                function (string $content) use ($targetPage) {
                    $targetPage->body = $content;
                    $this->targetRepo->storePage($targetPage);
                }
            )
        );
*/
        $this->client->interactive($aiTpl->getUserContent());
    }

}
