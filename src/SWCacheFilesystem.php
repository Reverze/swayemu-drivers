<?php

class SWCacheFilesystem implements SWCacheInterface
{
    private $rootCacheDirectory = null;
    private $filesystemCacheDirectory = null;
    private $appCacheDirectory = null;
    private $appCacheProfileFile = null;

    private $cacheVisibility = 'public';

    private $appCacheProfileName = null;
    private $appCacheCreateTime = null;
    private $appCacheModifyTime = null;

    public function __construct($cacheVisibilityMode = 'public') {
        $this->cacheVisibility = $cacheVisibilityMode;

        $this->rootCacheDirectory = MKernel::$tmp_path . 'cache';
        $fileSystemCacheDirectory = $this->rootCacheDirectory . DIRECTORY_SEPARATOR . 'filesystem';

        if (!is_dir($fileSystemCacheDirectory)){
            $createDirectoryResult = mkdir($fileSystemCacheDirectory);

            if (!$createDirectoryResult){
                throw new SWPermissionException ("Cannot create directory 'filesystem'. Path: '" . $fileSystemCacheDirectory . "'");
            }

        }

        if (is_dir($fileSystemCacheDirectory)){
            $this->filesystemCacheDirectory = $fileSystemCacheDirectory;

            $appCacheDirectory = (new SWPath())->path($this->filesystemCacheDirectory . '/' . SwayEngine::$app->appIdentifier);

            if (!is_dir($appCacheDirectory)){
                $createDirectoryResult = mkdir($appCacheDirectory);

                if (!$createDirectoryResult){
                    throw new SWPermissionException ("Cannot create directory '" . SwayEngine::$app->appIdentifier .
                        "'. Path: '" . $appCacheDirectory . "'");
                }
            }

            if (is_dir($appCacheDirectory)){
                $this->appCacheDirectory = $appCacheDirectory;
                $this->initializeAppCacheProfile();
            }

        }
    }

    private function initializeAppCacheProfile()
    {
        $this->appCacheProfileFile = $this->appCacheDirectory . DIRECTORY_SEPARATOR . 'profile.json';

        if (!is_file($this->appCacheProfileFile)){
            $result = $this->createAppCacheProfile();

            if (!$result){
                throw new SWWriteFileException ("Cannot create file 'profile.json' on Path: '" . $this->appCacheDirectory . "'");
            }
        }

        if (is_file($this->appCacheProfileFile)){
            $appCacheProfile = $this->getAppCacheProfile();

            if (is_array($appCacheProfile)){
                $this->appCacheProfileName = $appCacheProfile['name'];
                $this->appCacheCreateTime = $appCacheProfile['createTime'];
                $this->appCacheModifyTime = $appCacheProfile['modifyTime'];

                if ($appCacheProfile['visibility'] === 'private'){
                    if (SwayEngine::$app->appIdentifier !== $this->appCacheProfileName){
                        throw new SWPermissionException ("Cannot access to private cache '" . $this->appCacheProfileName . "'");
                    }
                }
            }
        }
    }

    /**
     * Get content of cache at specified path. Example cache path: index.body.headers
     * @param type $cachePath
     */
    public function get($cachePath)
    {
        $cachePathTree = explode (".", $cachePath);

        foreach ($cachePathTree as $cachePathNode){
            if (!preg_match('/^[a-zA-Z0-9\-\_]+/', $cachePathNode)){
                throw new SWInvalidPathException ("Cache path is invalid! '" . $cachePath . "'");
            }
        }

        $cacheSystemPath = str_replace('.', DIRECTORY_SEPARATOR, $cachePath);

        $cacheSystemPath = (new SWPath())->path($this->appCacheDirectory . DIRECTORY_SEPARATOR . $cacheSystemPath);

        $tmpCacheBuilder = $this->appCacheDirectory . DIRECTORY_SEPARATOR;
        for ($i = 0; $i < sizeof($cachePathTree) - 1; $i++){
            $tmpCacheBuilder .= $cachePathTree[$i] . DIRECTORY_SEPARATOR;
        }

        $cacheFilePath = $tmpCacheBuilder . $cachePathTree[sizeof($cachePathTree) - 1];

        if (!is_file($cacheFilePath)){
            throw new SWReadException ("No cache on path: '" . $cachePath ."'");
        }

        if (is_file($cacheFilePath)){
            $readResult = SWFile::readFile($cacheFilePath);
            $jsonArray = json_decode($readResult, true);

            if (is_array($jsonArray))
                return $jsonArray;
            else
                return $readResult;
        }

    }

    public function set($cachePath, $data)
    {
        $cachePathTree = explode (".", $cachePath);

        foreach ($cachePathTree as $cachePathNode){
            if (!preg_match('/^[a-zA-Z0-9\-\_]+/', $cachePathNode)){
                throw new SWInvalidPathException ("Cache path is invalid! '" . $cachePath . "'");
            }
        }

        $cacheSystemPath = str_replace('.', DIRECTORY_SEPARATOR, $cachePath);
        $cacheSystemPath = (new SWPath())->path($this->appCacheDirectory . DIRECTORY_SEPARATOR . $cacheSystemPath);

        $tmpCacheBuilder = $this->appCacheDirectory . DIRECTORY_SEPARATOR;
        for ($i = 0; $i < sizeof($cachePathTree) - 1; $i++){
            $tmpCacheBuilder .= $cachePathTree[$i] . DIRECTORY_SEPARATOR;

            if (!is_dir($tmpCacheBuilder)){
                $createDirectoryResult = mkdir($tmpCacheBuilder);

                if (!$createDirectoryResult){
                    throw new SWPermissionException ("Cannot create directory. Path: '" . $tmpCacheBuilder . "'");
                }
            }
        }

        $cacheFilePath = $tmpCacheBuilder . $cachePathTree[sizeof($cachePathTree) - 1];

        if (!is_file($cacheFilePath)){
            $writeResult = SWFile::writeFile($cacheFilePath, "\n");
        }

        if (is_file($cacheFilePath)){
            $writeResult =  SWFile::writeFile($cacheFilePath,
                (is_array($data) ? json_encode($data) : (string) $data));

            if (!$writeResult){
                throw new SWWriteFileException ("Cannot write data to file: '" . $cacheFilePath . "'" );
            }
        }
    }

    private function getAppCacheProfile()
    {
        $jsonStringify = SWFile::readFile($this->appCacheProfileFile);

        $jsonData = json_decode($jsonStringify, true);

        if (is_array($jsonData)){
            return $jsonData;
        }
        else{
            throw new SWParseException ("Cannot parse profile from json to array");
        }
    }

    private function createAppCacheProfile()
    {
        $cacheProfileData = array();
        $cacheProfileData['name'] = SwayEngine::$app->appIdentifier;
        $cacheProfileData['visibility'] = $this->cacheVisibility;
        $cacheProfileData['createTime'] = (int) time();
        $cacheProfileData['modifyTime'] = (int) time();

        return SWFile::writeFile($this->appCacheProfileFile, json_encode($cacheProfileData));
    }

}

?>