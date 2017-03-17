<?php

class SWStringGenerator
{
    const LatinCharset = "qazwsxedcrfvtgbyhnujmiklop";
    const NumericalCharset = "0123456789";
    const SpecialCharset = "~`!@#$%^&*()_+|-=\\{}[]:;'\"<>,.?/";


    /**
     * Options:
     * ['latin'] = true/false (default true)
     * ['numeric'] = true/false (default false)
     * ['latinRegex'] = regex pattern (default: none)
     * ['numericRegex'] = regex pattern (default: none)
     * ['special'] = true/false (default: false)
     * ['specialRegex'] = regex pattern (defaeult: none)
     */

    /**
     *
     * @param type $options
     */
    public static function Generate($length = 16, $options = array())
    {
        $charset = "";

        $settings = array();
        $settings['latin'] = true;
        $settings['numeric'] = false;
        $settings['latinRegex'] = '';
        $settings['numericRegex'] = '';
        $settings['special'] = false;
        $settings['specialRegex'] = '';

        if (isset($options['latin']))
        {
            if ($options['latin'] === true)
                $settings['latin'] = true;
            else if ($options['latin'] === false)
                $settings['latin'] = false;
            else
                trigger_error ("Unrecognized value of setting");
        }

        if (isset($options['numeric']))
        {
            if ($options['numeric'] === true)
                $settings['numeric'] = true;
            else if ($options['numeric'] === false)
                $settings['numeric'] = false;
            else
                trigger_error ("Unrecognized value of setting");
        }

        if (isset($options['special']))
        {
            if ($options['special'] === true)
                $settings['special'] = true;
            else if ($options['special'] === false)
                $settings['special'] = false;
            else
                trigger_error ("Unrecognized value of setting");
        }

        if (isset($options['latinRegex']))
        {
            if (is_string($options['latinRegex']))
            {
                if (strlen($options['latinRegex']) > 0)
                    $settings['latinRegex'] = $options['latinRegex'];
            }
            else
            {
                trigger_error ("Undefined type of latinRegex. Must be string");
            }
        }

        if (isset($options['numericRegex']))
        {
            if (is_string($options['numericRegex']))
            {
                if (strlen($options['numericRegex']) > 0)
                    $settings['numericRegex'] = $options['numericRegex'];
            }
            else
            {
                trigger_error ("Undefined type of numericRegex. Must be string");
            }
        }

        if (isset($options['specialRegex']))
        {
            if (is_string($options['specialRegex']))
            {
                if (strlen($options['specialRegex']) > 0)
                    $settings['specialRegex'] = $options['specialRegex'];
            }
            else
            {
                trigger_error ("Undefined type of specialRegex. Must be string");
            }
        }

        if ($settings['latin'] === true)
        {
            if (strlen($settings['latinRegex']) > 0)
            {
                $latinCharset = self::LatinCharset;

                for ($pointer = 0; $pointer < strlen($latinCharset); $pointer++)
                    if (preg_match($settings['latinRegex'], $latinCharset[$pointer]))
                        $charset .= $latinCharset[$pointer];
            }
            else
            {
                $charset .= self::LatinCharset;
            }
        }

        if ($settings['numeric'] === true)
        {
            if (strlen($settings['numericRegex']) > 0)
            {
                $numericCharset = self::NumericalCharset;

                for ($pointer = 0; $pointer < strlen($numericCharset); $pointer++)
                    if (preg_match($settings['numericRegex'], $numericCharset[$pointer]))
                        $charset .= $numericCharset[$pointer];
            }
            else
            {
                $charset .= self::NumericalCharset;
            }

        }

        if ($settings['special'] === true)
        {
            if (strlen($settings['specialRegex']) > 0)
            {
                $specialCharset = self::SpecialCharset;

                for ($pointer = 0; $pointer < strlen($specialCharset); $pointer++)
                    if (preg_match($settings['specialRegex'], $specialCharset[$pointer]))
                        $charset .= $specialCharset[$pointer];

            }
            else
            {
                $charset .= self::SpecialCharset;
            }
        }

        if (strlen($charset) > 0)
        {
            $generatedString = "";

            for ($pointer = 0; $pointer < $length; $pointer++)
            {
                $generatedPointer = rand(0, (strlen($charset)) - 1);
                $generatedString .= $charset[$generatedPointer];
            }

            return $generatedString;
        }
        else
        {
            trigger_error ("Cannot generate random string. Input charset is empty");
        }
    }

}

?>
