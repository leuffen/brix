<?php

namespace Leuffen\Brix\Plugins\Angebot;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Type\BrixState;
use Phore\Cli\CLIntputHandler;
use Phore\FileSystem\PhoreDirectory;

class AngebotCreator
{
    public function __construct (private LackOpenAiClient $client, private PhoreDirectory $rootDir, private BrixState $state) {

    }
    


    public function extractDataPrompt(string $userContent)
    {
        $tpl = new JobTemplate(__DIR__ . "/extract_data.prompt.txt");

        $tpl->setData([
            "userContent" => $userContent
        ]);

        $this->client->reset($tpl->getSystemContent());

        $result = $this->client->textComplete($userContent, streamOutput: true);

        $this->rootDir->withFileName("current_user_data.txt")->set_contents($result);
        echo "\nGespeichert unter current_user_data.txt\n";
    }


    public function create()
    {
        $cli = new CLIntputHandler();
        
        $userData = $cli->askString("Bitte geben Sie die Daten ein");
        if ($userData === "")
            return;
        
        $tpl = new JobTemplate(__DIR__ . "/angebotprompt.txt");

        $tpl->setData([
            "demo_angebot" => $this->rootDir->withFileName("demo_angebot.md")->get_contents(),
            "userContent" => $userData
        ]);


        $this->client->reset("Heute ist der " . date("d.m.Y") . ". " . $tpl->getSystemContent());

        $result = $this->client->textComplete($tpl->getUserContent(), streamOutput: true)->getTextCleaned();

        $this->rootDir->withRelativePath("current")->assertDirectory(true);
        
        $this->rootDir->withFileName("out.md")->set_contents($result);
    }
    
    public function save() {
        $path = $this->rootDir->withRelativePath("history")
            ->withRelativePath(date("Y"))
            ->withRelativePath($this->state->incr("angebot"))
    }
}
