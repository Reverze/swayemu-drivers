<?php


class SwayExPage
{

    private static $config = array();


    private static $templates = array();


    private static $template = "global";

    private static $_echo_dumps = array();

    public static $Enabled = true;

    protected static $_to_call_functions = array();

    public static function Start()
    {
        self::loadConfig();
        ob_start();
        register_shutdown_function(array("SwayExPage", "onExit"));
        self::$_to_call_functions['on_exit'] = array();
    }


    public static function Disable()
    {
        self::$Enabled = false;
    }

    public static function selectTemplate($template_name)
    {
        return self::_selectTemplate($template_name);
    }

    /**
     * This function append new listener for onExit Event
     * @param type $to_call
     * @throws SWRuntimeException
     */
    public static function onExitEvent($to_call)
    {
        if (!is_array($to_call) AND !is_string($to_call))
        {
            $exception = new SWRuntimeException('SwayExPage: $to_call can be only array or string');
            throw $exception;
        }
        else if (is_array($to_call) OR is_string($to_call))
        {
            array_push(self::$_to_call_functions['on_exit'], $to_call);
        }
    }

    protected static function _selectTemplate($template_name)
    {
        if (isset(self::$config['OnlyGlobalErrorPage']))
        {
            if ((bool) self::$config['OnlyGlobalErrorPage'] === TRUE)
                self::$template = "global";
            else
                self::$template = $template_name;
        }
        else
            self::$template = "global";
    }

    protected static function loadConfig()
    {
        $config_path = MKernel::$etc_path . 'expage.ini';

        if (is_file($config_path))
        {
            $ini = parse_ini_file($config_path, TRUE);

            if (is_array($ini))
            {
                #parsing configuration

                $object = $ini["Global"];

                foreach ($object as $key => $val)
                    self::$config[$key] = $val;


                #parsing templates

                $object = $ini['ErrorPages'];

                foreach ($object as $key => $val)
                {
                    $template_config_path = MKernel::$etc_path . 'expage' . DIRECTORY_SEPARATOR . $val . DIRECTORY_SEPARATOR . $val . '.ini';

                    if (is_file($template_config_path))
                    {
                        $tmp = parse_ini_file($template_config_path);

                        if (isset($tmp['path']))
                        {
                            $template = array();
                            $template['name'] = $key;
                            $template['path'] = MKernel::$etc_path . 'expage' . DIRECTORY_SEPARATOR . $val . DIRECTORY_SEPARATOR  . $tmp['path'];

                            if (is_file($template['path']))
                            {
                                self::$templates[$key] = $template['path'];
                            }
                            else
                            {
                                $exception = new SWFileFoundException("File not found");
                                $exception->path = $template['path'];
                                throw $exception;
                            }

                        }
                        else
                        {
                            $exception = new SWRuntimeException("Cannot find variable 'path' is error page template configuration file");
                            $exception->class = $template_config_path;
                            throw $exception;
                        }

                    }
                    else
                    {
                        $exception = new SWFileFoundException("File not found");
                        $exception->path = $template_config_path;
                        throw $exception;
                    }
                }

            }
            else
            {
                $exception = new SWRuntimeException("Configuration file is damaged");
                throw $exception;
            }
        }
        else
        {
            $exception = new SWFileFoundException("File not found");
            $exception->path = $config_path;
            throw $exception;
        }

    }

    protected static function loadConfigOverride()
    {

        if (is_file(SwayEngine::$app->workingDirectory . DIRECTORY_SEPARATOR . 'expage.ini'))
        {
            $parseIniFile = parse_ini_file(SwayEngine::$app->workingDirectory . DIRECTORY_SEPARATOR . 'expage.ini');


            if (isset($parseIniFile['output_path_override']))
            {
                $outputPathOverride = (string) $parseIniFile['output_path_override'];

                if (strlen($outputPathOverride))
                {
                    $basePath = SwayEngine::$app->workingDirectory;

                    $exploded = explode("../", $outputPathOverride);

                    $parentNodes = (int) sizeof($exploded) - 1;

                    for ($i = 0; $i < $parentNodes; $i++)
                        $basePath = dirname($basePath);

                    $realPath = $basePath . DIRECTORY_SEPARATOR . $exploded[sizeof($exploded) - 1];

                    self::$config['output_path_override'] = $realPath;

                }
            }

        }



    }


    public static function debugWrite($string = "")
    {
        array_push (self::$_echo_dumps, $string);
    }

    protected static function flushErrorPage($user_errors, $runtime_errors, $exception_errors)
    {
        global $SW_USER;

        #loading template
        $template_source = "";
        $fp = fopen(self::$templates[self::$template], "r");

        $template_source = fread($fp, filesize(self::$templates[self::$template]));

        if (strlen($template_source) > 0)
        {
            $console_output = "";

            if (count($user_errors) > 0)
            {
                $counter = 1;

                foreach ($user_errors as $user_error)
                {
                    $console_output .= $counter . ' <strong>[UserError]</strong> ';
                    $console_output .= ' <strong>Type: </strong>' . $user_error['errno'];
                    $console_output .= ', ' . $user_error['errstr'];
                    $console_output .= ', <strong>File: </strong>';
                    $console_output .= $user_error['errfile'];
                    $console_output .= ', <strong>Line: </strong> ';
                    $console_output .= $user_error['errline'] . ';<br/>';

                    $counter++;
                }
            }

            if (count($runtime_errors) > 0)
            {

                $console_output .= "<strong>RuntimeError [" . $runtime_errors['type'] . ']</strong>';
                $console_output .= " " . $runtime_errors['message'];
                $console_output .= ', <strong>File: </strong> ' . $runtime_errors['file'];
                $console_output .= ', <strong>Line: </strong> ' . $runtime_errors['line'] . ';<br/>';


            }

            if (count($exception_errors) > 0)
            {
                foreach ($exception_errors as $exeptionObject)
                {
                    $console_output .= '<strong>Exception [' . $exeptionObject->getCode() . ']</strong>';
                    $console_output .= ' <strong>Message: </strong> "' . $exeptionObject->getMessage() . '"';
                    $console_output .= ' (' . $exeptionObject->getTraceAsString() . ')';
                    $console_output .= ', <strong>File: </strong> ' . $exeptionObject->getFile();
                    $console_output .= ', <strong>Line: </strong> ' . $exeptionObject->getLine() . ';<br />';
                }
            }

            if (isset($SW_USER))
            {
                if ($SW_USER instanceof SWUser)
                {
                    if ($SW_USER->IsLogged())
                    {
                        if ($SW_USER->HaveFlag('a'))
                        {
                            SwayExPageParser::define('FLAG_A');

                            $template_source = SwayExPageParser::Parse($template_source);
                        }
                    }
                }
            }


            $template_source = str_replace("@error_output", $console_output, $template_source);

            $template_source = SwayExPageParser::trimMacrosFromSource($template_source);



            ob_clean();


            echo $template_source;

        }

        fclose($fp);

    }

    public static function readErrorsFile()
    {
        self::loadConfigOverride();

        if (isset(self::$config['output_path_override']))
        {
            SWLog::$path = self::$config['output_path_override'];
        }

        return SWFile::readFile(self::$config['output_path_override'] . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'ExPageReoutput.swl');

    }

    /* Ta metoda zostanie wywolana w chwili zakonczenia wykonywania skryptu */
    public static function onExit()
    {
        if (self::$Enabled === FALSE)
            return;

        self::loadConfigOverride();

        if (isset(self::$config['output_path_override']))
        {
            SWLog::$path = self::$config['output_path_override'];
        }

        if (isset(self::$_to_call_functions['on_exit']))
        {
            $user_errors = SwayErrorHandler::getUserErrors();
            $runtime_errors = SwayErrorHandler::getRuntimeErrors();
            $uncaught_exceptions = SwayErrorHandler::getUncaughtExceptions();



            if (count($user_errors) > 0 OR count($uncaught_exceptions) > 0 OR count($runtime_errors) > 0)
            {
                foreach ($user_errors as $user_error)
                {

                    $outputFile = "";
                    $outputFile .= " [Warning!] ";
                    $outputFile .= 'Type: ' . (strlen($user_error['errno']) > 0 ?
                            $user_error['errno'] : "undefined");
                    $outputFile .= " " . (strlen($user_error['errstr']) > 0 ?
                            $user_error['errstr'] : "undefined");
                    $outputFile .= " File: " . (strlen($user_error['errfile']) > 0 ?
                            $user_error['errfile'] : "undefined");
                    $outputFile .= " on line: " . (strlen($user_error['errline']) > 0 ?
                            $user_error['errline'] : "undefined" ) . "\n";

                    SWLog::AppendLog('ExPageReoutput', $outputFile, "", "", "", "");
                }

                if (count($runtime_errors) > 0)
                {
                    $outputFile = "";
                    $outputFile .= "[RuntimeError!] ";
                    $outputFile .= " Type: " . (strlen($runtime_errors['type']) > 0 ?
                            $runtime_errors['type'] : "undefined");
                    $outputFile .= " Message: " . (strlen($runtime_errors['message']) > 0 ?
                            $runtime_errors['message'] : "undefined");
                    $outputFile .= " File: " . (strlen($runtime_errors['file']) > 0 ?
                            $runtime_errors['file'] : 'undefined' );
                    $outputFile .= " on line: " . (strlen($runtime_errors['line']) > 0 ?
                            $runtime_errors['line'] : 'undefined'). "\n";

                    SWLog::AppendLog('ExPageReoutput', $outputFile, "", "", "", "");

                }


                if (is_array($uncaught_exceptions) > 0)
                {
                    foreach ($uncaught_exceptions as $exceptionObject)
                    {
                        $outputFile = "";
                        $outputFile .= '<strong>Exception [' . $exceptionObject->getCode() . ']</strong>';
                        $outputFile .= ' <strong>Message: </strong> "' . $exceptionObject->getMessage() . '"';
                        $outputFile .= ' (' . $exceptionObject->getTraceAsString() . ')';
                        $outputFile .= ', <strong>File: </strong> ' . $exceptionObject->getFile();
                        $outputFile .= ', <strong>Line: </strong> ' . $exceptionObject->getLine() . ";\n";

                        SWLog::AppendLog('ExPageReoutput', $outputFile, "", "", "", "");
                    }
                }



            }

            if (count(self::$_to_call_functions['on_exit']) > 0)
            {
                foreach (self::$_to_call_functions['on_exit'] as $function)
                {
                    call_user_func ($function);
                }

                return;
            }
        }



        /* Sprawdzanie czy SwayErrorHandler jest zaladowany */
        if (class_exists("SwayErrorHandler"))
        {
            if (SwayEngine::$LoadMode === SwayEngineLoadModeCollection::DEFAULT_MODE)
            {
                $user_errors = SwayErrorHandler::getUserErrors();
                $runtime_errors = SwayErrorHandler::getRuntimeErrors();
                $uncaught_exceptions = SwayErrorHandler::getUncaughtExceptions();

                if (count($user_errors) > 0 OR count ($runtime_errors) > 0 OR count($uncaught_exceptions) > 0)
                {
                    self::flushErrorPage($user_errors, $runtime_errors, $uncaught_exceptions);
                }

            }
            else if (SwayEngine::$LoadMode === SwayEngineLoadModeCollection::CRON_MODE)
            {
                $console_output = "";
                if (count(self::$_echo_dumps) > 0)
                    foreach (self::$_echo_dumps as $echodump)
                        $console_output .= '-->ECHO_DUMP: ' . $echodump . "\n";



                $user_errors = SwayErrorHandler::getUserErrors();
                $runtime_errors = SwayErrorHandler::getRuntimeErrors();
                $uncaught_exceptions = SwayErrorHandler::getUncaughtExceptions();

                if (count($user_errors) > 0 OR count($runtime_errors) > 0 OR count($uncaught_exceptions) > 0)
                {
                    if (strlen($console_output) === 0)
                        $console_output = "An few errors has excepted\n";
                    else
                        $console_output .= "\nAn few errors has excepted\n";

                    foreach ($user_errors as $user_error)
                    {
                        $console_output .= "Warning: ";
                        $console_output .= "Type: " . (strlen($user_error['errno']) > 0 ?
                                $user_error['errno'] : "undefined");
                        $console_output .= " " . (strlen($user_error['errstr']) > 0 ?
                                $user_error['errstr'] : "undefined");
                        $console_output .= " File: " . (strlen($user_error['errfile']) > 0 ?
                                $user_error['errfile'] : "undefined");
                        $console_output .= " on line: " . (strlen($user_error['errline']) > 0 ?
                                $user_error['errline'] : "undefined" ) . "\n";
                    }

                    if (is_array($runtime_errors))
                    {
                        if (count($runtime_errors) > 0)
                        {
                            $console_output .= "RuntimeError: ";
                            $console_output .= " Type: " . (strlen($runtime_errors['type']) > 0 ?
                                    $runtime_errors['type'] : "undefined");
                            $console_output .= " Message: " . (strlen($runtime_errors['message']) > 0 ?
                                    $runtime_errors['message'] : "undefined");
                            $console_output .= " File: " . (strlen($runtime_errors['file']) > 0 ?
                                    $runtime_errors['file'] : 'undefined' );
                            $console_output .= " on line: " . (strlen($runtime_errors['line']) > 0 ?
                                    $runtime_errors['line'] : 'undefined'). "\n";

                        }
                    }

                    foreach ($uncaught_exceptions as $uncaught_exception)
                    {
                        $console_output .= "Exception: ";
                    }

                    ob_clean();

                    echo $console_output;

                    return;
                }

                if (count(self::$_echo_dumps) > 0)
                {
                    ob_clean();

                    echo $console_output;
                    return;
                }

            }
        }

    }

}

?>