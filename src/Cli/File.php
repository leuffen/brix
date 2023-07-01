<?php

namespace Leuffen\Brix\Cli;

use Lack\Keystore\Type\Service;
use Lack\OpenAi\Helper\JobDescription;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Business\BrixEnvFactorySingleton;
use Leuffen\Brix\Functions\FileAccessFunctions;
use Leuffen\Brix\Functions\GoogleMapsFunctions;
use Leuffen\Brix\Functions\PythonFunctions;
use Leuffen\Brix\Functions\SingleFileAccessFunctions;
use Leuffen\Brix\Functions\UserInteractiveFunctions;
use Leuffen\Brix\Type\BrixEnv;
use Phore\Cli\Exception\CliException;

class File
{

    private LackOpenAiClient $client;

    private JobDescription $jobDescription;

    private SingleFileAccessFunctions $singleFileAccessFunctions;

    private BrixEnv $brixEnv;

    public function __construct() {
        $this->brixEnv = $brixEnv = BrixEnvFactorySingleton::getInstance()->getEnv();
        $this->client = $client = $brixEnv->getOpenAiApi();

        $this->jobDescription = $jobDescription = new JobDescription();
        $jobDescription->addContext($brixEnv->contextCombined);
        $jobDescription->addRule("Use only defined functions. Never call python.");
        $jobDescription->addRule("Use <CONTEXT> as information source.");
        $jobDescription->addRule("If you don't know the answer, check if it can be retrieved by api call, otherwise insert [data-missing].");
        $jobDescription->addRule("Always preserve structure, whitespace and comments when interacting with yaml / json.");
        $jobDescription->addRule("Follow instructions provided as comments in yaml / json files.");
        $jobDescription->addRule("Always ask if you don't understand the task or options are unclear.");
        $jobDescription->addRule("Always query input data using readData() and write modified data using writeData().");

        $client->reset($jobDescription);
        $client->addClass(new GoogleMapsFunctions($brixEnv->keyStore->getAccessKey(Service::GoogleMaps)));
        $client->addClass($this->singleFileAccessFunctions = new SingleFileAccessFunctions(getcwd()));
    }

    private function getDataFormat(string $fileExtension) {
        switch ($fileExtension) {
            case "yml":
            case "yaml":
                return "YAML-Format";
            case "json":
                return "JSON-Format";
            case "md":
                return "Frontmatter Markdown-Format";
            default:
                return "Format";
        }
    }

    public function alter ($argv, string $task = null) {
        if (count($argv) !== 1)
            throw new CliException("alter [filename] expects exact 1 parameter");


        /*
        $prompt =  "What is the format that the readData() function provided as input data? ";
        $prompt .= "Imagine only the structure (without data) of the data including whitespace and comments looks like.";
        $prompt .= "Imagine the data from context <CONTEXT> was the same structure as the input data if original whitespace and commends where preserved but all the old data was overwritten?";
        $prompt .= "What values have to be changed to have consistent data? Which functions have to be called to load additional data?";
        $prompt .= "Execute all steps and write modified data to datasource. Always keep the format.";
*/

        //$task .= ". What data-format is the input data loaded with readData()? How would this input data look if we replaced the values with data from <CONTEXT> without altering the structure or comments, and if the instructions from comments within the input data were applied. Load missing data via api calls. Save it using writeData(). Always keep the format.";




        $files = glob($argv[0]);
        foreach ($files as $file) {

            $this->singleFileAccessFunctions->setFiles($file, $file, phore_file($file)->getExtension());

            $prompt = "Load input data in {$this->singleFileAccessFunctions->getDataFormat()} from datasource! Replace all invdividual data in the input data with data extracted from <CONTEXT> while keeping the structure and comments intact, and applying any instructions mentioned in the comments. Use available APIs to autoload data. Write the resul to datasource. It is important to maintain the original format throughout this process";

            $this->client->reset($this->jobDescription);
            $this->client->interactive($prompt);
        }

    }


     public function structure ($argv) {
        if (count($argv) !== 1)
            throw new CliException("alter [filename] expects exact 1 parameter");


        $files = glob($argv[0]);
        foreach ($files as $file) {
            $file = phore_file($file);
            $this->singleFileAccessFunctions->setFiles($file, $file,);
            $dataFormat = $this->getDataFormat($file->getExtension());

            $prompt = "Load {$dataFormat} data from datasource. Remove any individual data (values, names, etc). Replace Names and personal information inside text by '(Insert <type of data> here)'. Imagine the data-structure without any data in it. Output the data-structure. Preserve the format, whitespace and comments. Save the structure in valid {$dataFormat} to datasource.";


            $this->client->reset($this->jobDescription);
            $this->client->textComplete($prompt, streamOutput: true);
        }
    }

    public function fill ($argv) {
        if (count($argv) !== 1)
            throw new CliException("alter [filename] expects exact 1 parameter");


        $files = glob($argv[0]);
        foreach ($files as $file) {
            $file = phore_file($file);
            $this->singleFileAccessFunctions->setFiles($file, $file,);
            $dataFormat = $this->getDataFormat($file->getExtension());

            //$prompt = "Your job is to load data in {$dataFormat} from datasource. Fill in data provided in context. Finally save the data als valid $dataFormat to datasource. The Context data: \"\"\"{$this->brixEnv->contextCombined}\"\"\". Preserve the format, whitespace and comments.";

            echo $prompt;

            $this->client->reset();

            $this->client->textComplete("Start by loading the {$dataFormat} inputdata from datasource.", streamOutput: true);
            $this->client->textComplete("Now replace the values in inputdata with following contextual data: '''{$this->brixEnv->contextCombined}''''. Preserve the structure, whitspace, comments of inputdata. ", streamOutput: true);

            $this->client->textComplete("Is there any data that needs to be reloaded from api to make sense? Perform the api calls! ", streamOutput: true);
            $this->client->textComplete("Write the modified data in valid {$dataFormat} to datasource ", streamOutput: true);

        }
    }

    public function create (string $example_file, $argv) {
        if (count($argv) !== 1)
            throw new CliException("alter [filename] expects exact 1 parameter");

        $example_file = phore_file($example_file)->assertFile();


        $openAi = new LackOpenAiClient($this->brixEnv->keyStore->getAccessKey(Service::OpenAi));

        $file = $argv[0];
        $file = phore_file($file);
        $this->singleFileAccessFunctions->setFiles($example_file, $file);
        $dataFormat = $this->getDataFormat($file->getExtension());


        $prompt = "Imagine the example provided in $dataFormat: \"\"\"{$example_file->get_contents()}\"\"\" \n was filled with information from: <CONTEXT>{$this->brixEnv->contextCombined}</CONTEXT>. \nKeep witespace, comments and structure. Output valid {$dataFormat}.";
        $prompt = "Use the example provided in: \"\"\"{$example_file->get_contents()}\"\"\" \n to generate a new file for the context: <CONTEXT>{$this->brixEnv->contextCombined}</CONTEXT>. Replace all texts from example with new ones. Keep witespace, comments and structure. Output valid {$dataFormat}.";


        $output = $openAi->textComplete($prompt, streamOutput: true);

        $file->set_contents($output);
    }
}
