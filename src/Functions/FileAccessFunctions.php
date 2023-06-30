<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;
use Phore\FileSystem\PhoreDirectory;

class FileAccessFunctions
{

    public PhoreDirectory $rootDir;

    public function __construct(string|PhoreDirectory $rootDir) {
        $this->rootDir = phore_dir($rootDir);
    }

    /**
     * @param string $filter
     * @return void
     */
    #[AiFunction("Get list of available file uris. ")]
    public function listFiles(
        #[AiParam("Filter files by pattern e.g. *.md")]string $filter = null
    ) : array
    {
        return $this->rootDir->listFiles($filter, true);
    }

    #[AiFunction("Return raw string contents of file specified in parameter 'uri'. Returns error message on failure.")]
    public function get_file_contents(

        #[AiParam("The full uri of the file")]string $uri
    ) {
        try {
            return $this->rootDir->withRelativePath($uri)->asFile()->get_contents();
        } catch (\Exception $e) {
            return "ERROR: ". $e->getMessage();
        }
    }

    #[AiFunction("Set contents of file. Returns success / error message")]
    public function set_file_contents(
        #[AiParam("The files uri to set content")] string $uri,
        #[AiParam("The raw string contents to be written to file")] string $content
    ) {
        try {
            $this->rootDir->withRelativePath($uri)->asFile()->set_contents($uri, $content);
            echo "File contents successful written to $uri";
        } catch (\Exception $e) {
            return "ERROR: ". $e->getMessage();
        }
    }

}
