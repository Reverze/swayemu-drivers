<?php

class SWPath
{
    public function __construct()
    {
        return $this;
    }

    public function path($pathString, $currentDir = null)
    {
        $pathInput = $pathString;

        $pathInput = str_replace("/", DIRECTORY_SEPARATOR, $pathInput);
        $pathInput = str_replace("\\", DIRECTORY_SEPARATOR, $pathInput);

        if (is_string($currentDir) && $currentDir !== null)
        {
            $basePath = $currentDir;

            $exploded = explode(".." . DIRECTORY_SEPARATOR, $pathInput);

            $parentNodes = (int) sizeof($exploded) - 1;

            for ($i = 0; $i < $parentNodes; $i++)
                $basePath = dirname($basePath);

            $realPath = $basePath . DIRECTORY_SEPARATOR . $exploded[sizeof($exploded) - 1];

            return $realPath;
        }
        else
        {
            return $pathInput;
        }
    }

    public function pathArray($pathArray, $currentDir = null)
    {
        return $this->path(join(DIRECTORY_SEPARATOR, $pathArray), $currentDir);
    }
}

?>