<?php

class SWResourceCombiner
{
    private $resourcesIncludePath = null;
    private $resourcesType = null;
    private $resourcesConfigFilePath = null;
    private $resourcesController = null;

    public function __construct()
    {

    }

    public function setResourcesIncludePath($resourcesIncludePath)
    {
        if (!is_dir($resourcesIncludePath)){
            throw new Exception ("Path not found. Path: '" . $resourcesIncludePath . "'");
        }
        else{
            $this->resourcesIncludePath = $resourcesIncludePath;
        }
    }

    public function setResourcesType($resourcesType = SWResource::JAVASCRIPT_RESOURCES)
    {
        $this->resourcesType = $resourcesType;
    }

    public function setResourcesConfigFilePath($resourcesConfig)
    {
        if (!is_file($resourcesConfig)){
            throw new Exception ("File on path: '" . $resourcesConfig . "' not found");
        }
        else{
            $this->resourcesConfigFilePath = $resourcesConfig;
        }
    }

    public function getCombinedResources()
    {
        if (empty($this->resourcesConfigFilePath)){
            throw new Exception ("Resources config file path is not set");
        }

        if (empty($this->resourcesIncludePath)){
            throw new Exception ("Resources include path is not set");
        }

        if (empty($this->resourcesType)){
            throw new Exception ("Resources type is not defined");
        }

        if ($this->resourcesType === SWResource::JAVASCRIPT_RESOURCES){
            $this->resourcesController = new SWResourceJavaScriptCombiner($this->resourcesIncludePath,
                $this->resourcesConfigFilePath);

            return $this->resourcesController->getResource();
        }
        else{
            throw new Exception ("Resources type is unsupported. Defined as: '" .
                $this->resourcesType . "'");
        }
    }



}


?>
