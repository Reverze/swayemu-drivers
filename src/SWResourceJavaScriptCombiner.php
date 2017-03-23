<?php

class SWResourceJavaScriptCombiner
{
    private $resourcesIncludePath = null;
    private $resourcesConfigFilePath = null;
    private $resourcesConfigArray = null;

    private $resourcesSource = "";

    public function __construct($includePath, $configFilePath)
    {
        $this->resourcesIncludePath = $includePath;
        $this->resourcesConfigFilePath = $configFilePath;

        $this->combineResources();
    }

    private function combineResources()
    {
        if (empty($this->resourcesConfigArray)){
            $this->initializeConfigFile();
        }

        if (sizeof($this->resourcesConfigArray)){
            foreach ($this->resourcesConfigArray as $scriptName => $scriptOptions){
                $scriptSrc = $this->resourcesIncludePath . DIRECTORY_SEPARATOR .
                    $scriptOptions['src'];

                if (is_file($scriptSrc)){
                    $scriptSource = SWFile::readFile($scriptSrc);
                    $this->resourcesSource .= $scriptSource;
                }
                else{
                    throw new Exception ("File not found on path: '" . $scriptSrc . "'");
                }

            }
        }

    }

    public function getResource()
    {

        return $this->resourcesSource;
    }

    private function initializeConfigFile()
    {

        $jsonStringify = SWFile::readFile($this->resourcesConfigFilePath);

        if (!is_string($jsonStringify)){
            throw new Exception ("Cannot read resources config file");
        }

        $jsonData = json_decode($jsonStringify, true);

        if (!is_array($jsonData)){
            throw new Exception ("Cannot parse data (json) to array");
        }
        else{
            $this->resourcesConfigArray = $jsonData;
        }
    }

}

?>
