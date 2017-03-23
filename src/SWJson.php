<?php

/**
 * Json wrapper for build-in php's json parser
 * This class require SWFile module
 */

class SWJson
{

    public function __construct()
    {


    }

    protected function parseJsonRawSource (string $jsonRawSource)
    {
        $jsonArray = json_decode($jsonRawSource, true);

        /* Getting last json decode error */
        $jsonLastError = json_last_error();

        switch ($jsonLastError) {
            /* No errors returns, it's ok */
            case JSON_ERROR_NONE:
                return $jsonArray;
                break;
            /* The maximum specified stack depth has been exceeded */
            case JSON_ERROR_DEPTH:
                throw new SWJsonException('The maximum stack depth has been exceeded');
                break;
            /* Invalid or malformed JSON */
            case JSON_ERROR_STATE_MISMATCH:
                throw new SWJsonException('Invalid or malformed JSON');
                break;
            /* Control character error, possibly incorrectly encoded */
            case JSON_ERROR_CTRL_CHAR:
                throw new SWJsonException('Control character error, possibly incorrectly encoded');
                break;
            /* Json syntax error. Check configuration file for validity json syntax */
            case JSON_ERROR_SYNTAX:
                throw new SWJsonException('Syntax error');
                break;
            /* Malformed UTF-8 characters, possibly incorrectly encoded' */
            case JSON_ERROR_UTF8:
                throw new SWJsonException('Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            /* One or more recursive references in the value to be encoded */
            case JSON_ERROR_RECURSION:
                throw new SWJsonException('One or more recursive references in the value to be encoded');
                break;
            /* One or more NAN or INF values in the value to be encoded */
            case JSON_ERROR_INF_OR_NAN:
                throw new SWJsonException('One or more NAN or INF values in the value to be encoded');
                break;
            /* A value of a type that cannot be encoded was given */
            case JSON_ERROR_UNSUPPORTED_TYPE:
                throw new SWJsonException('A value of a type that cannot be encoded was given');
                break;
        }

        return $jsonArray;
    }

    public function parseJson(string $jsonRawSource)
    {
        try{
            return $this->parseJsonRawSource($jsonRawSource);
        }
        catch (Exception $ex) {
            throw $ex;
        }

    }

    public function parseFile(string $jsonFilePath)
    {
        try {
            $jsonRawSource = SWFile::readFile($jsonFilePath);
            return $this->parseJsonRawSource($jsonRawSource);
        }
        catch (Exception $ex){
            throw $ex;
        }

    }

}


?>
