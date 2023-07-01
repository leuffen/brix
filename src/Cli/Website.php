<?php

namespace Leuffen\Brix\Cli;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
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
        $example = $this->templateRepo->selectPid($pid, $lang)->get();

        $openAi = $this->brixEnv->getOpenAiApi();

        $openAi->addClass(new SingleFileAccessFunctions(
            fn() => "no data to read",
            fn($data) => $targetPage->body = $data

        ));

        $openAi->textComplete("You are about to write a website in markdown format. Analyze this example for later reproduction: '''{$example->body}'''.", streamOutput: true);
        //$openAi->textComplete("The context of the new website is: '''{$this->brixEnv->contextCombined}'''. Imagine the content from example was adjusted to match the needs of the websites context. But the structure, whitespaces and formatting in brackets are holy. Don't touch them.", streamOutput: true);
        $openAi->textComplete("Create a new website with exact the same structure and styling as the example. Adjust text in headings and paragraphs to match: '''{$this->brixEnv->contextCombined}'''. ", streamOutput: true);
        $openAi->textComplete("Save the imagined content to datasource. Preserve structure, whitespace and styling from example.", streamOutput: true);

        $this->targetRepo->storePage($targetPage);
    }
}
