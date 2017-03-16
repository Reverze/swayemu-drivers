<?php



/**
 * Wrapper for 'Spyc' library
 */
class YamlWrapper
{
    /**
     * Yaml file path
     * @var string
     */
    private $yamlFilePath = null;

    /**
     * Yaml string content
     * @var string
     */
    private $yamlContent = null;



    public function __construct()
    {
        /**
         * If vendor's class 'Spyc' not exists
         */
        if (!class_exists('Spyc')){
            throw new Exception (sprintf("Class '%s' not exists!", 'Spyc'));
        }

    }

    /**
     * Sets file's path
     * @param string $filePath
     * @return null
     * @throws \SWFileFoundException
     */
    public function setFile(string $filePath, string $workingDirectory = null)
    {
        $realPath = realpath($filePath);

        /**
         * Accepts only existing files
         */
        if (is_file($realPath)){
            $this->yamlFilePath = $realPath;
            return;
        }

        if (!empty($workingDirectory)){
            $realPath = realpath($workingDirectory . DIRECTORY_SEPARATOR . $filePath);

            if (is_file($realPath)){
                $this->yamlFilePath = $realPath;
                return;
            }
        }

        throw new Exception (sprintf("File on path '%s' not exists",
            realpath($realPath)));
    }

    /**
     * Sets yaml's content
     * @param string $yamlContent
     * @return null
     * @throws \SWEmptyException
     */
    public function setContent(string $yamlContent)
    {
        if (!empty($yamlContent) && strlen($yamlContent)){
            $this->yamlContent = $yamlContent;
            return;
        }

        throw new Exception ("Yaml's content is empty");
    }

    public function parse()
    {
        $yamlArray = array();

        /**
         * If file and content is not specified
         */
        if (empty($this->yamlFilePath) && empty($this->yamlContent)){
            throw new Exception ("You must specify file path or give yaml as string");
        }

        /** Loads from file */
        if (!empty($this->yamlFilePath)){
            $yamlArray = Spyc::YAMLLoad($this->yamlFilePath);
        }

        /** If load from string */
        if (!empty($this->yamlContent) && !sizeof($yamlArray)){
            $yamlArray = Spyc::YAMLLoadString($this->yamlContent);
        }

        if (isset($yamlArray['yaml.require'])){
            $extendedArray = $this->executeYamlRequire($yamlArray['yaml.require']);

            unset($yamlArray['yaml.require']);

            if (sizeof($extendedArray)){
                $yamlArray = array_merge($yamlArray, $extendedArray);
            }
        }

        return $this->recursiveSkimOver($yamlArray);

        return $yamlArray;
    }

    protected function recursiveSkimOver(array $yamlArray) : array
    {
        $outputArray = $yamlArray;

        foreach ($outputArray as $keyArray => $keyValue){

            if ($keyArray === 'yaml.require'){
                $requiredResources = $keyValue;

                if (is_array($keyValue)){
                    $extendedArray = $this->executeYamlRequire($keyValue);

                    /**
                     * This unset is probably not necessary
                     */
                    unset($outputArray['yaml.require']);

                    $outputArray = array_merge($outputArray, $extendedArray);
                }
                else{
                    throw new \Exception("Directive 'yaml.require' requires array");
                }


            }

            if (is_array($keyValue)){
                $resurived = $this->recursiveSkimOver($keyValue);

                unset($resurived['yaml.require']);
                $outputArray[$keyArray] = $resurived;
            }


        }

        return $outputArray;

    }


    protected function executeYamlRequire(array $externalResources)
    {
        $externalResourcesArray = array();

        foreach ($externalResources as $externalResource){
            $yamlWrapper = new YamlWrapper();
            $yamlWrapper->setFile($externalResource, dirname($this->yamlFilePath));

            $yamlArray = $yamlWrapper->parse();

            $externalResourcesArray = array_merge($externalResourcesArray, $yamlArray );
        }

        return $externalResourcesArray;
    }


}




?>