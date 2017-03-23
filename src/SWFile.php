<?php

class SWFile
{
    public static function readFile($filePath, $length = -1)
    {
        if (!is_file($filePath))
            throw new SWFileFoundException ('File not found on path: ' . $filePath);

        $readTo = 0;

        if ($length === -1)
            $readTo = filesize($filePath);
        else
            $readTo = $length;

        $buffer = "";

        $fp = fopen($filePath, "r");

        if ($fp === false)
            throw new SWFileHandlerException('Cannot create file handler for file on path: ' . $filePath);

        $buffer = fread($fp, $readTo);

        fclose($fp);

        return $buffer;
    }

    public static function writeFile($filePath, $data)
    {
        return file_put_contents($filePath, $data);
    }
}

?>