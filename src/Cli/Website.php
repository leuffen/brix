<?php

namespace Leuffen\Brix\Cli;

use Lack\Frontmatter\Repo\FrontmatterRepo;
use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Plugins\Seo\SeoAnalyzer;
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


        $systemRole = <<<EOT

You create content for a website using example sites. Some information about the owner of the website and the context of the website:

{$this->brixEnv->contextCombined}

Use this information only for reference. Don't use it to create content. It is important to keep the number and order of
structure of headings, blockquotes and paragraphs. Never edit styling specified in curved brackets {}. Preserve <hr> specified by ---.
Don't modify the number of heading or order of levels.

Preserve the language detected from example, structure, whitespaces, order and number of outline and formatting in brackets.
Don't modify the number of heading or order of levels. Never output original content. Suggest new content based on the context.
EOT;



        $prompt = <<<EOT

Replace the content from the example below with seo optimized text based on the websites context. Imagine
what the website owner would write if he was a seo and internet marketing expert knowing, that you write for
your audience.

{$systemRole}

"""{$example->body}"""

EOT;



        $targetPage->header = $example->header;
        $openAi->reset($systemRole);
        $targetPage->body = $openAi->textComplete($prompt, streamOutput: true);

       // $openAi->textComplete("use only defined functions. You are about to write a website in markdown format. Information about the website owner and context: '''{$this->brixEnv->contextCombined}'''.", streamOutput: true);
        //$openAi->textComplete("The context of the new website is: '''{$this->brixEnv->contextCombined}'''. Imagine the content from example was adjusted to match the needs of the websites context. But the structure, whitespaces and formatting in brackets are holy. Don't touch them.", streamOutput: true);
        //$targetPage->body = $openAi->textComplete("Modify the content of the template: '''{$example->body}''' to fit the new context. Write Seo-optimized content. Preserve the exact structure and formatting of the template. Return valid markdown.", streamOutput: true);

        /*
        $openAi->textComplete("You are about to write a website in markdown format. Analyze this example for later reproduction: '''{$example->body}'''.", streamOutput: true);
        //$openAi->textComplete("The context of the new website is: '''{$this->brixEnv->contextCombined}'''. Imagine the content from example was adjusted to match the needs of the websites context. But the structure, whitespaces and formatting in brackets are holy. Don't touch them.", streamOutput: true);
        $openAi->textComplete("Create a new website with exact the same structure and styling as the example. Adjust text in headings and paragraphs to match: '''{$this->brixEnv->contextCombined}'''. ", streamOutput: true);
        $openAi->textComplete("Save the created content to datasource.", streamOutput: true);
        */
        $this->targetRepo->storePage($targetPage);
        return;

        $analyzer = new SeoAnalyzer($this->brixEnv->getOpenAiApi());
        $result = $analyzer->analyze($targetPage->body);
        $targetPage->header["title"] = $result->title;
        $targetPage->header["description"] = $result->metaDescription;
        $targetPage->header["keywords"] = implode(", ", $result->keywords);
        $targetPage->header["seoScore"] = $result->qualityScore;

        echo "\nPage created: $pid\n";
        echo "Seo Score: {$result->qualityScore} (bad 1 - 10 best)\n";
        echo "Optimizations:\n";
        foreach ($result->optimizations as $opt) {
            echo " - $opt\n";
        }
        $this->targetRepo->storePage($targetPage);


    }

    public function list(array $argv, string $lang = "de") {
        $filter = $argv[0] ?? "*";
        echo "\nList availabe template pid:\n";
        $pages = $this->templateRepo->list($filter, $lang);
        foreach ($pages as $page) {
            echo ">" . $page . "\n";
        }
    }


    public function templatify (array $argv, string $lang = "de") {
        if (count($argv) !== 1)
            throw new CliException("templatify [pid] expects exact 1 parameter");

        $pid = $argv[0];

        $template = $this->templateRepo->selectPid($pid, $lang)->get();

        $prompt = <<<EOT

Analyze the structure of the document. Create a template with the same structure and formatting.
Replace all text (including headings, blockquotes, etc) with a abstract description of the content (length, structure, formatting, etc.). Keep the
original structure and formatting. Return valid markdown.

\"\"\"
{$template->body}
\"\"\"

EOT;

        $openAi = $this->brixEnv->getOpenAiApi();
        $template->body = $openAi->textComplete($prompt, streamOutput: true);
    }

}
