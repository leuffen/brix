<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Phore\FileSystem\PhoreFile;

class SingleFileAccessFunctions
{

    private string|null $dataFormat = "raw";

    public function __construct(
        public string|\Closure|PhoreFile $file,
        public string|\Closure|PhoreFile|null $outFile = null
    )
    {
        $this->setFiles($this->file, $this->outFile);
    }


    public function getDataFormat() : string {
        return $this->dataFormat;
    }


    public function setFiles(
        string|\Closure|PhoreFile $file,
        string|\Closure|PhoreFile|null $outFile = null,
        string|null $dataFormat = null
    ){
        if ( ! ($file instanceof \Closure)) {
            $this->file = phore_file($file);
        }
        if ( ! ($outFile instanceof \Closure)) {
            $this->outFile = phore_file($outFile ?? $file);
        }
    }

    #[AiFunction("Load input data from datasource. Use only this function to load input data. Returns data as string.")]
    public function readData() {
        if ($this->file instanceof \Closure)
            return ($this->file)();
        return $this->file->get_contents();
    }

    #[AiFunction("Write data to datasource. Usage: `writeData(content : string) : void`")]
    public function writeData(#[AiParam("Content to save to datasource. Required!")] string $content) {
        if ($this->outFile instanceof \Closure)
            return ($this->outFile)($content);
        return $this->outFile->set_contents($content);
    }


}
