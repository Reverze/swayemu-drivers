<?php

class SwayErrorHandler
{
    /**
     * Stores all occurred errors defined by user
     * @var array
     */
    protected static $user_errors = array();

    /**
     * Stores all occurred php errors
     * @var array
     */
    protected static $php_errors = array();

    /**
     * Stores all uncaughted exceptions
     * @var \Exception[]
     */
    protected static $uncaught_exceptions = array();


    /**
     * Registers event listeners
     */
    public static function Start()
    {
        register_shutdown_function(array("SwayErrorHandler", "onPHPError"));
        set_error_handler(array("SwayErrorHandler", "onUserError"));
        set_exception_handler(array("SwayErrorHandler", "onUncaughtException"));
    }

    public static function onUserError($errno, $errstr, $errfile, $errline, $err_context)
    {
        $error = array();
        $error['errno'] = $errno;
        $error['errstr'] = $errstr;
        $error['errfile'] = $errfile;
        $error['errline'] = $errline;
        $error['errcontext'] = $err_context;

        array_push(self::$user_errors, $error);
    }

    public static function onPHPError()
    {
        self::$php_errors = error_get_last();
    }

    public static function onUncaughtException (\Exception $exception)
    {
        array_push(self::$uncaught_exceptions, $exception);
    }

    public static function getUserErrors()
    {
        return self::$user_errors;
    }

    public static function getRuntimeErrors()
    {
        return self::$php_errors;
    }

    public static function getUncaughtExceptions()
    {
        return self::$uncaught_exceptions;
    }
}

?>