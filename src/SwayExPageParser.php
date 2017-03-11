<?php

class SwayExPageParser
{
    protected static $pMacros = array();
    protected static $definedConstants = array();

    const MacrosNotDefined = 0x54D;

    public static function define($name)
    {
        array_push(self::$definedConstants, $name);
    }

    public static function Parse($templateSourceCode)
    {
        $xmlParseOutput = self::parseMacros();

        if ($xmlParseOutput === FALSE)
            return FAlSE;


        if (strlen($templateSourceCode) <= 0)
            return false;
        else
        {
            $linesArray = explode ("\n", $templateSourceCode);



            $outputSource = "";

            if (is_array($linesArray))
            {
                foreach ($linesArray as $line)
                {
                    $macroline = $line;

                    if (strpos($line, '@PMACRO') !== FALSE)
                    {
                        $macroline = str_replace("@PMACRO", "", $line);

                        $wordsT = explode (' ', $macroline);

                        if (in_array("IFSET", $wordsT))
                        {
                            $ifsetCondition = "";
                            $afterCondition = false;

                            $afterDO = false;
                            $doCondition = "";

                            $words = array();

                            foreach ($wordsT as $temp)
                            {
                                if (strlen($temp) > 0)
                                    array_push($words, $temp);
                            }


                            for ($pointer = 0; $pointer < count($words); $pointer++)
                            {
                                if ($words[$pointer] === "IFSET")
                                    $ifsetCondition .= $words[$pointer + 1];
                                if ($words[$pointer] === "DO:")
                                    $doCondition .= $words[$pointer + 1];

                            }


                            if (strlen($doCondition) > 0 && strlen($ifsetCondition) > 0)
                            {

                                if (in_array($ifsetCondition, self::$definedConstants))
                                {
                                    if (isset(self::$pMacros[trim($doCondition)]))
                                        $macroline = self::$pMacros[trim($doCondition)];
                                    else
                                        $macroline = "";
                                }
                                else
                                    $macroline = "";
                            }


                        }

                    }

                    $outputSource .= $macroline . "\n";

                }

                return $outputSource;
            }
            else
                return false;

        }

    }

    public static function trimMacrosFromSource($source)
    {
        $linesArray = explode("\n", $source);

        $outputSource = "";

        foreach ($linesArray as $line)
            if (!preg_match('/^@PMACRO.*$/', $line))
                $outputSource .= $line;

        return $outputSource;

    }

    protected static function parseMacros($directoryTemplateName = 'global')
    {
        $macrosPath = MKernel::$etc_path . 'expage' . DIRECTORY_SEPARATOR . $directoryTemplateName
            . DIRECTORY_SEPARATOR . 'macros.xml';


        if (!is_file($macrosPath))
            return self::MacrosNotDefined;
        else
        {
            $xmlParseOutput = simplexml_load_file($macrosPath);


            foreach ($xmlParseOutput->pmacro as $pmacro)
                self::_parsePMacroElement ($pmacro);


            return TRUE;

        }

    }

    protected static function _parsePMacroElement($xmlElement)
    {
        if (get_class($xmlElement) === 'SimpleXMLElement')
            foreach ($xmlElement->attributes() as $key => $value)
                if ((string)$key === 'name')
                    self::$pMacros[strtoupper((string) $value)] = (string) $xmlElement;

        return FALSE;

    }


}


?>