<?php

namespace Leuffen\Brix\Plugins\Content;

use Lack\Frontmatter\FrontmatterPage;
use Lack\OpenAi\Helper\JobTemplate;
use Lack\OpenAi\LackOpenAiClient;

class ContentCreator
{

    public function __construct(
        public string $context,
        public LackOpenAiClient $client
    ){

    }

    public function brainstormText(string $subject)
    {
        $tpl = new JobTemplate(__DIR__ . "/brainstorm-text.txt");

        $tpl->setData([
            "subject" => $subject,
            "context" => $this->context,
            "language" => "DE",
            "length" => 1500,
        ]);

        $this->client->reset($tpl->getSystemContent());
        return $this->client->textComplete($tpl->getUserContent(), streamOutput: true)->getTextCleaned();
    }



    public function suggestTextStructure(string $subject)
    {


        $tpl = new JobTemplate(__DIR__ . "/suggest-text-structure.txt");

        $tpl->setData([
            "subject" => $subject,
            "context" => $this->context,
            "language" => "DE",
            "length" => 1500,
        ]);

        $this->client->reset($tpl->getSystemContent());
        return $this->client->textComplete($tpl->getUserContent(), streamOutput: true)->getTextCleaned();
    }


    public function suggestMetaInformation(string $text) : T_MetaData {
         $tpl = new JobTemplate(__DIR__ . "/metadata.txt");

        $tpl->setData([
            "content" => $text,
            "context" => $this->context
        ]);

        $this->client->reset($tpl->getSystemContent());
        return $this->client->textComplete($tpl->getUserContent(), streamOutput: true)->getJson(T_MetaData::class);
    }

    public function applyTextToTemplate (string $template, string $content) : string {
        $tpl = new JobTemplate(__DIR__ . "/applyToTemplate.txt");

        $tpl->setData([
            "content" => $content,
            "context" => $this->context
        ]);

        $this->client->reset($tpl->getSystemContent());
        return $this->client->textComplete([$template, $tpl->getUserContent()], streamOutput: true)->getTextCleaned();
    }

    public function runForPage(FrontmatterPage $page) {
        if ( ! isset ($page->header["_ai_content"]) || ! is_array($page->header["_ai_content"]) ) {
            return;
        }

        $aiContent = $page->header["_ai_content"];
        $subject = $aiContent["subject"] ?? "";
        $prompt = $aiContent["prompt"] ?? "";
        $templatePid = $aiContent["template_pid"] ?? "";

        $brainstorm = $this->brainstormText($subject);
        $structure = $this->suggestTextStructure($brainstorm . $subject);
        $meta = $this->suggestMetaInformation($brainstorm . $structure);

        $page->header["title"] = $meta->title;
        $page->header["description"] = $meta->description;
        $page->header["keywords"] = implode ( " ", $meta->keywords);
        $page->header["short_title"] = $meta->short_title;
        //$page->header["permalink"] = $meta->permalink;
        $page->header["image"] = $meta->image;

        $page->body = $this->applyTextToTemplate($page->body, $structure);


    }


}
