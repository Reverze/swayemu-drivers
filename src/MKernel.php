<?php

/**
 * @Author Reverze (hawkmedia24@gmail.com)
 *
 * This packages emulates behaviour of memria kernel.
 * If you want to use drivers bundled with 'swayemu-drivers' you must create a virtual kernel.
 */


class MKernel
{
    /**
     * This directory contains configuration file for all parts on kernel and swayengine
     * @var type
     */
    public static $etc_path = "";
    /**
     * This directory contains temporary data and others files
     * @var type
     */
    public static $var_path = "";
    /**
     * This is root directory
     * @var type
     */
    public static $root_path = "";

    /**
     * This directory contains temponary data. This directory is required to properly work tempcontainer driver.
     * @var type
     */
    public static $tmp_path = "";


    public function __construct(string $virtualKernelWorkPath)
    {
        /**
         * If directory is not exists, create a new one
         */
        if (!is_dir($virtualKernelWorkPath)){
            $makeDirResult = mkdir($virtualKernelWorkPath, 0755);

            if (!$makeDirResult){
                throw new MKernelException(sprintf("Cannot create directory on path: '%s'", $virtualKernelWorkPath));
            }
        }

        static::$root_path = $virtualKernelWorkPath;
        static::$etc_path = sprintf("%s/%s", $virtualKernelWorkPath, 'etc');
        static::$var_path = sprintf("%s/%s", $virtualKernelWorkPath, 'var');
        static::$tmp_path = sprintf("%s/%s", $virtualKernelWorkPath, 'tmp');

        $this->checkDirectoryStructure();
    }

    private function checkDirectoryStructure()
    {
        if (!is_dir(static::$etc_path)){
            $makeDirResult = mkdir(static::$etc_path, 0755);

            if (!$makeDirResult){
                throw new MKernelException(sprintf("Cannot create directory on path: '%s'", static::$etc_path));
            }
        }

        if (!is_dir(static::$var_path)){
            $makeDirResult = mkdir(static::$var_path, 0755);

            if (!$makeDirResult){
                throw new MKernelException(sprintf("Cannot create directory on path: '%s'", static::$var_path));
            }
        }

        if (!is_dir(static::$tmp_path)){
            $makeDirResult = mkdir(static::$tmp_path, 0755);

            if (!$makeDirResult){
                throw new MKernelException(sprintf("Cannot create directory on path: '%s'", static::$tmp_path));
            }
        }
    }

}

?>