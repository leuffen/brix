<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Phore\FileSystem\PhoreFile;

class SingleFileAccessFunctions
{

    private string|null $dataFormat = "raw";

    public function __construct(
        public string|PhoreFile $file,
        public string|PhoreFile|null $outFile = null
    )
    {
        $this->setFiles($this->file, $this->outFile);
    }


    public function getDataFormat() : string {
        return $this->dataFormat;
    }


    public function setFiles(
        string|PhoreFile $file,
        string|PhoreFile|null $outFile = null,
        string|null $dataFormat = null
    ){
        $this->file = phore_file($file);
        $this->outFile = phore_file($outFile ?? $file);
    }

    #[AiFunction("Load input data from datasource. Use only this function to load input data. Returns data as string.")]
    public function readData() {
        return $this->file->get_contents();
    }

    #[AiFunction("Write modified data to datasource. Use to save modified data.")]
    public function writeData(#[AiParam("New content as raw string.")] string $newContent) {
        return $this->outFile->set_contents($newContent);
    }


}
