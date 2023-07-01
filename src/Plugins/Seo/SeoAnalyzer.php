<?php

namespace Leuffen\Brix\Plugins\Seo;

use Lack\OpenAi\LackOpenAiClient;

class SeoAnalyzer
{

    public function __construct (private LackOpenAiClient $client){

    }


    public function analyze(string $text) : SeoAnalyzerResult {

        $prompt = <<<PROMPT

You analyze the following content of a website:

"""
$text
"""

Think of a seo optimal meta description, title, keywords, a quality score 1-10 where 10 is the best and a
list of possible text-optimizations to improve the seo score.

Respond pure json of type `Result`:

type Result = {
    metaDescription: string,
    title: string,
    keywords: string[],
    qualityScore: number,
    optimizations: string[]
}


PROMPT;


        $output = $this->client->textComplete($prompt, streamOutput: true);
        return phore_json_decode($output, SeoAnalyzerResult::class);
    }

}
