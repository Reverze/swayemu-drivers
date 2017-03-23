<?php

class SWCompare
{
    public static function compareor()
    {
        $numPassedArgs = func_num_args();

        if ($numPassedArgs > 1)
        {
            for ($pointer = 1; $pointer < $numPassedArgs; $pointer++)
                if (func_get_arg($pointer) === func_get_arg(0))
                    return true;

            return false;
        }
    }

    public static function compareand()
    {
        $numPassedArgs = func_num_args();

        if ($numPassedArgs > 1)
        {
            $toAnd = $numPassedArgs - 1;
            $counter = 0;

            for ($pointer = 1; $pointer < $numPassedArgs; $pointer++)
                if (func_get_arg($pointer) === func_get_arg(0))
                    $counter++;

            if ($counter === $toAnd)
                return true;
            else
                return false;


        }
    }



}


?>