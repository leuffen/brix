<?php

namespace Leuffen\Brix\Type;

use Phore\FileSystem\PhoreFile;

class BrixState
{

    public function __construct(private PhoreFile $file, private string $scope) {

    }


    private function loadData() {
        if ( ! $this->file->exists())
            return [];
        return $this->file->get_yaml();
    }

    private function saveData(array $data) {
        $this->file->set_yaml($data);
    }


    public function get(string $key) : mixed
    {
        $data = $this->loadData();
        if ($data[$this->scope][$key] ?? null === null)
            return null;
        return $data[$this->scope][$key];
    }

    public function set(string $key, $data) : void {
        $data = $this->loadData();
        if ( ! isset ($data[$this->scope]))
            $data[$this->scope] = [];
        $data[$this->scope][$key] = $data;
        $this->saveData($data);
    }

}
