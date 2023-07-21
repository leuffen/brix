<?php

namespace Leuffen\Brix\Plugins\Angebot;

use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;
use Leuffen\Brix\Plugins\ContextReplace\ContextReplace;
use Leuffen\Brix\Plugins\ExtractUserData\ExtractUserData;
use Leuffen\Brix\Plugins\RechtschreibungCorrect\RechtschreibungCorrect;
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
        $current = $this->rootDir->withRelativePath("current")->assertDirectory(true);
        
        $userData = $cli->askMultiLine("Bitte geben Sie die Daten ein");
        if ($userData === "")
            return;        
        
        $userData = (new RechtschreibungCorrect($this->client))->correct($userData);
        
        $tpl = new JobTemplate(__DIR__ . "/angebotprompt.txt");
        $tpl->setData([
            "demo_angebot" => $this->rootDir->withFileName("demo_angebot.md")->get_contents(),
            "userContent" => $userData
        ]);
        $this->client->reset("Heute ist der " . date("d.m.Y") . ". " . $tpl->getSystemContent());
        $result = $this->client->textComplete($tpl->getUserContent(), streamOutput: true)->getTextCleaned();
        
        $current->withFileName("angebot.md")->set_contents(trim($result));

        $current->withFileName("email.txt")->set_contents((new ContextReplace($this->client))->contextReplace($this->rootDir->withFileName("demo_email.txt")->get_contents(), $userData));
        
    }

    public function save() {
        
        $cli = new CLIntputHandler();
        $angebotId = $this->state->increment("angebotId");
        
        $name = $cli->askLine("Bitte geben Sie einen Namen für das Angebot (id: $angebotId) ein");
        if ($name === "")
            return;
        
        $targetPath = $this->rootDir->withRelativePath("history")->withRelativePath(date("Y"))->withRelativePath($name . "_" . $angebotId)->assertDirectory(true);
        $path = $this->rootDir->withRelativePath("current")->assertDirectory()->copyTo($targetPath);
        $this->state->increment("angebotId");
        echo "\nAngebot gespeichert unter: " . $path->getUri() . "\n";
            
    }
}
