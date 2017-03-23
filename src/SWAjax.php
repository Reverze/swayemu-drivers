<?php

/* This class require driver: SwayExPage */

class SWAjax
{
    protected static $ajax_headers = array();
    protected static $notices = array();
    public static $ajaxGetVariableName = 'ajax';
    protected static $background_content = array();
    protected static $isEnabled = false;

    /**
     * @var \Throwable[]
     */
    private static $uncaughtedExceptions = array();

    private static $occurredErrors = array();

    public static function Start()
    {
        set_exception_handler(function(Throwable $throwable){
             array_push(static::$uncaughtedExceptions, $throwable);
        });

        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext){
            $error = [
                'errno' => $errno,
                'errstr' => $errstr,
                'errfile' => $errfile,
                'errline' => $errline
            ];

            array_push(self::$occurredErrors, $error);
        });
        register_shutdown_function(array("SWAjax", "flushAsJSONFormat"));
        self::$isEnabled = true;
    }

    /**
     * This function will be called when SwayExPage raise event 'onFlush'.
     * This function will subsitue calling flushing function's SwayExPage
     *
     * returned JSON format:
     * {
     *      erorrs: "FALSE/{objectArray}",
     *      #if errors: { user-defined-errors }
     *      headers: {user-defined-errors}
     *      foreground-content: { {stringObject}user-defined-foreground-content/{arrayObject}user-defined-foreground-content }
     *      background-content: { {stringObject}user-defined-background-content/{arrayObject}user-defined-background-content}
     *      notices: {objectArray}
     * }
     */

    public static function isEnabled()
    {
        return self::$isEnabled;
    }


    /**
     * This method append notice for ajax response. Method gets notice code. Only integers value are allowed
     * @param type $notice_code
     */
    public static function appendNotice($notice_code)
    {
        if (is_int($notice_code))
            array_push(self::$notices, $notice_code);
        else if (is_string($notice_code))
        {
            if (!preg_match('/^[0-9]+$/', $notice_code))
                throw new SWInvalidParameter('Notice code accepts only integers value');
            else
                array_push (self::$notices, (int)$notice_code);
        }
    }

    /**
     * This method append header for ajax response. Header's key are required, key's value is optional
     * @param type $key Header's key
     * @param type $value Key's value
     */
    public static function appendHeader($key, $value = "")
    {
        if (!preg_match('/^[a-zA-z0-9]+$/', $key))
            throw new SWInvalidParameter('Headers\'s key can consits of the characters of the latin alphabet and digits');
        else
        {
            $tmp = "";

            if (strlen($value) > 0)
            {
                if (!preg_match('/^[a-zA-Z0-9]+$/', $value))
                    throw new SWInvalidParameter('Key\'s value can consits only of the characters of the latin alphabet and digits');
                else
                    $tmp = $value;
            }

            if (isset(self::$ajax_headers[$key]))
                throw new SWAlreadyExistsException('Another header use this key');
            else
                self::$ajax_headers[$key] = $tmp;

            unset($tmp);
        }
    }

    /**
     *
     * @param type $key
     * @param type $value
     */
    public static function appendBackgroundContent($key, $value)
    {
        self::$background_content[$key] = $value;
    }

    /**
     * This method flush ajax response
     */
    public static function flushAsJSONFormat()
    {

        $ajax_output = array();
        $ajax_output['errors'] = array();
        $ajax_output['headers'] = array();
        $ajax_output['notices'] = array();

        $user_errors = self::$occurredErrors;
        $runtime_errors = self::$uncaughtedExceptions;

        if (count($user_errors) > 0 OR count($runtime_errors) > 0)
            $ajax_output['errors'] = array("websiteError");

        $ajax_output['headers'] = self::$ajax_headers;

        $content = ob_get_clean(); #get content from echo etc. and clean the buffer

        $ajax_output['foreground-content'] = $content;
        $ajax_output['background-content'] = (array) self::$background_content;
        $ajax_output['notices'] = self::$notices;

        ob_clean();

        echo json_encode($ajax_output);



    }


}


?>