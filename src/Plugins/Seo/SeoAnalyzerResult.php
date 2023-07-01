<?php

namespace Leuffen\Brix\Plugins\Seo;

class SeoAnalyzerResult
{
    public string $metaDescription;
    public string $title;
    /**
     * @var string[]
     */
    public array $keywords;

    public int $qualityScore;
    /**
     * @var string[]
     */
    public array $optimizations;


}
