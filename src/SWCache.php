<?php


class SWCache
{
    private $cacheWorkingDirectory = "";
    private $supportedCacheSystem = ['filesystem'];

    private $cacheSystemController = null;

    public function __construct($mode = 'filesystem', $visibility = 'public')
    {
        $tmpRootDirectory = MKernel::$tmp_path;
        $cacheDirectory = MKernel::$tmp_path . 'cache';

        if (!is_dir($cacheDirectory)){
            $createCacheDirectoryResult = mkdir($cacheDirectory);

            if (!$createCacheDirectoryResult){
                throw new SWPermissionException ("Cannot create directory 'cache'. Path: '" . $cacheDirectory . "'");
            }
        }

        $this->cacheWorkingDirectory = $cacheDirectory;

        if ($mode === 'filesystem'){
            $this->cacheSystemController = new SWCacheFilesystem($visibility);
        }
        else{
            throw new SWUnsupportedException ("Cache mode '" . $mode . "' is unsupported");
        }

    }

    private function isOthersGroupSavePermission($permissionChmodNumeric, $type = 'directory')
    {
        $tmp = str_split($permissionChmodNumeric);
        $permission = $tmp[sizeof($tmp) - 1];

        if ($type === 'directory'){
            if ((int) $permission === 7)
                return true;
            else
                return false;
        }

        if ($type === 'file'){
            if ((int) $permission === 7 or (int) $permission === 6)
                return true;
            else
                return false;
        }
    }

    public function get($cachePath)
    {
        return $this->cacheSystemController->get($cachePath);
    }

    public function set($cachePath, $data)
    {
        return $this->cacheSystemController->set($cachePath, $data);
    }

}

?>