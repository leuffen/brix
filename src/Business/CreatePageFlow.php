<?php

namespace Leuffen\Brix\Business;

use Cassandra\Date;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Api\OpenAiApi;
use Leuffen\Brix\Type\T_BrixConfig;
use Phore\FileSystem\PhoreDirectory;

class CreatePageFlow
{

    public function __construct(
        private T_BrixConfig $config,
        private PhoreDirectory $brixDir,
        private LackOpenAiClient $openAiApi,
        private string $contextCombined = ""
    )
    {}


    public function createPage(string $template) {

    }

    public function runTaskFile($taskFile) {
        $taskFile = phore_file($taskFile)->abs()->assertFile();
        $name = explode(".", $taskFile->getFilename())[0] ?? "undefined";
        echo "Running task file: $taskFile\n";

        $targetFile = $taskFile->withParentDir()->withFileName("$name.de.md");
        echo "Target file: $targetFile\n";
        $prompt = "";

        $prompt .= file_get_contents(__DIR__ . "/../prompts/create_page_flow_prompt.txt");
        $prompt .= "\n\nContext:\n" . $this->config->context;
        $prompt .= "\n\nCurrent Task: Create Site content with: \n" . $taskFile->get_contents();

        $targetFile->touch();
        $targetFile->set_contents(
            $this->openAiApi
                ->textComplete($prompt, streamOutput: true);
        );

    }


    public function compactFile($taskFile) {
        $taskFile = phore_file($taskFile)->abs()->assertFile();
        $prompt = "";
        $prompt .= file_get_contents(__DIR__ . "/../prompts/compact_prompt.txt");
        $prompt .= $taskFile->get_contents();
        $data = $this->openAiApi
            ->textComplete($prompt, streamOutput: true)
            ->getText();

        echo "\nSave changes?";
        $input = readline(" [y/N] ");
        if (trim ($input) !== "y")
            return;

        $taskFile->set_contents(
            $data
        );
    }

    public function patch($file) {
        $file = phore_file($file)->abs()->assertFile();

        $prompt = file_get_contents(__DIR__ . "/../prompts/patch_prompt.txt");
        $prompt = str_replace("%input%", $file->get_contents(), $prompt);
        $prompt = str_replace("%context%", $this->contextCombined, $prompt);

        $task = readline("Task: ");
        $prompt = str_replace("%task%", $task, $prompt);

        echo $prompt;
        $data = $this->openAiApi
            ->textComplete($prompt, streamOutput: true);

    }

      public function patchFile($file) {
        $file = phore_file($file)->abs()->assertFile();

        $prompt = file_get_contents(__DIR__ . "/../prompts/patch_comment_prompt.txt");
        $prompt = str_replace("%input%", $file->get_contents(), $prompt);
        $prompt = str_replace("%context%", $this->contextCombined, $prompt);

        $data = $this->openAiApi
            ->textComplete($prompt, streamOutput: true);

    }

}
