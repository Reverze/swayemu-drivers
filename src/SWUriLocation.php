<?php

/**
 * This class is helpful to create user-friendly urls location
 */
class SWUriLocation
{
    protected static $enabled = false;

    protected static $locations = array();
    protected static $directory_flags = array();
    protected static $others = array();
    protected static $location_separator = '/';
    protected static $root_path_uri_location = '';
    protected static $variables_get = array();
    protected static $current_directory_in_path = "";

    /**
     * This method will start recognition locations by url
     */
    public static function Parse()
    {
        self::$enabled = false;
        self::$locations = array();
        self::$directory_flags = array();
        self::$others = array();
        self::$variables_get = array();
        self::$current_directory_in_path = "";

        if (!isset($_SERVER["QUERY_STRING"]))
        {
            $exception = new Exception("'QUERY_STRING' is not set in global variable '_SERVER'");
            throw $exception;
        }
        else if (isset($_SERVER['QUERY_STRING']))
        {
            #dzielenie zapytania do serwera na flagi, argumenty i lokalizacje
            #mozliwe dzielenie na & i przecinek
            $splitted = preg_split('/[&]+/', $_SERVER['QUERY_STRING']);



            $location_path = "";
            $gets = array();




            foreach ($splitted as $e)
            {

                if (preg_match('/^' . self::$root_path_uri_location . '.*$/', $e))
                    $location_path = $e;
                else
                    array_push($gets, $e);
            }




            #examples for get's variables
            #ajax (without value)
            #ajax:{42} (variable with value)

            foreach ($gets as $get_variable)
            {
                $temp = preg_split('/[:]/', $get_variable);




                if (is_array($temp))
                {

                    $key = (isset($temp[0]) ? $temp[0] : FALSE);
                    $value_tmp = (isset($temp[1]) ? $temp[1] : null);

                    $value_tmp = str_replace('{', '', $value_tmp);
                    $value_tmp = str_replace('}', '', $value_tmp);

                    if (strpos($value_tmp, ',') !== FALSE)
                    {
                        $values_tmp = preg_split('/[,]+/', $value_tmp);

                        if (is_array($values_tmp))
                        {

                            $t = array();

                            foreach ($values_tmp as $entry)
                            {
                                if (preg_match('/^[0-9]+[.]*[0-9]*$/', $entry))
                                    array_push($t, (double) $entry);
                                else
                                    array_push($t, $entry);

                            }

                            $value_tmp = $t;
                        }
                    }



                    if ($key !== FALSE)
                        self::$variables_get[$key] = $value_tmp;

                }
            }


            if (strlen($location_path) <= 0)
            {
                self::$locations = array();
            }
            else if (strlen($location_path) > 0)
            {
                $directories = explode(self::$location_separator, $location_path);

                self::$locations = $directories;
            }

            self::$enabled = true;

        }
    }

    /**
     * Return status of uri location. If returned value if TRUE, means that is ready to use
     * @return type
     */
    public static function isReady()
    {
        return self::$enabled;
    }

    /**
     * This method sets separator for location path. Only single char is allowed
     * @param type $separator
     */
    public static function setSeperatorForLocationPath($separator)
    {
        if (strlen($separator) > 1)
        {
            $exception = new Exception('Only single char is allowed, setting separator for location path');
            throw $exception;
        }
        else if (strlen($separator) <= 0)
        {
            $exception = new Exception('Setting separator as empty char is not allowed');
            throw $exception;
        }
        else if (strlen($separator) === 1)
        {
            $allowed_separators = array(";", "", "/",);

            if (!in_array($separator, $allowed_separators))
            {
                $exception = new Exception('Setted separator for location path is not allowed');
                throw $exception;
            }
            else
            {
                self::$location_separator = $separator;
            }
        }
    }

    /**
     * This method sets a current directory at path
     * @param type $path
     */
    public static function setCurrentDirectory($path)
    {
        self::$current_directory_in_path = $path;
    }

    /**
     * This method return first directory in path
     * @param type $path
     * @return type
     */
    public static function getGrandParent()
    {
        if (isset(self::$locations[0]))
            return self::$locations[0];
        else
            return FALSE;
    }

    /**
     * This method checks if specified variable is set
     * @param type $variable_name
     * @return boolean
     */
    public static function issetVariable($variable_name)
    {
        if (isset(self::$variables_get[$variable_name]))
            return true;
        else
            return false;
    }

    /**
     * This method returns value of variable
     * @param type $variable_name
     * @return boolean
     */
    public static function getVariable($variable_name)
    {
        if (isset(self::$variables_get[$variable_name]))
            return self::$variables_get[$variable_name];
        else
            return FALSE;
    }

    /**
     * This method sets a parent path for location
     * @param type $path
     */
    public static function setRootPathForLocation($path)
    {
        if (strlen($path) <= 0)
        {
            $exception = new Exception('You cannot set empty path as parent path');
            throw $exception;
        }
        else if (strlen($path) > 0)
        {
            self::$root_path_uri_location = $path;
            self::$current_directory_in_path = $path;
        }

    }

    /**
     * Returns children directory of current directory in location path
     * @return type
     */
    public static function getChildren()
    {
        $pointer_parent = 0;
        $pointer_children = 0;

        #getting pointer for parent
        for ($pointer = 0; $pointer < count(self::$locations); $pointer++)
            if ( strval(self::$locations[$pointer]) === self::$current_directory_in_path )
                $pointer_parent = $pointer;

        $size = count(self::$locations) - 1;

        $pointer_children = $pointer_children + 1;

        if ($pointer_children > $size)
            return null;
        else
            return (isset(self::$locations[$pointer_children]) ? self::$locations[$pointer_children] : null);

    }

    /**
     * Returns parent of children
     * @return type
     */
    public static function getParent()
    {
        $pointer_parent = 0;
        $pointer_children = 0;

        for ($pointer = 0; $pointer < count(self::$locations); $pointer++)
            if ( strval(self::$locations[$pointer]) === self::$current_directory_in_path )
                $pointer_children = $pointer;

        $size = count(self::$locations) - 1;

        $pointer_parent = $pointer_children - 1;

        if ($pointer_parent < 0)
            return null;
        else
            return (isset(self::$locations[$pointer_parent]) ? self::$locations[$pointer_parent] : null);

    }

    /**
     *
     * @param type $children_name
     * @return boolean
     */
    public static function isChildren($children_name)
    {
        if (self::getChildren() === strval($children_name))
            return TRUE;
        else
            return FALSE;
    }

    public static function isLocation($locationName)
    {

        foreach (self::$locations as $location)
        {
            if ($location === $locationName)
                return TRUE;
        }


        return FALSE;
    }
}

?>
