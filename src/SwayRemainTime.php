<?php

class SwayRemainTime
{
    /**
     * Return a timeleft in days, hours, minutes, seconds
     * @param type $timeleft
     * @param type $remain_format
     */
    public static function Calculate($timeleft, $remain_format = array())
    {
        $calculate_days = true;
        $calculate_hours = true;
        $calculate_minutes = true;
        $calculate_seconds = true;
        $string_format = true;

        if (isset($remain_format['days']))
            if ($remain_format['days'] === false)
                $calculate_days = false;

        if (isset($remain_format['hours']))
            if ($remain_format['hours'] === false)
                $calculate_hours = false;

        if (isset($remain_format['minutes']))
            if ($remain_format['minutes'] === false)
                $calculate_minutes = false;

        if (isset($remain_format['seconds']))
            if ($remain_format['seconds'] === false)
                $calculate_seconds = false;

        if (isset($remain_format['string_format']))
            if ($remain_format['string_format'] === false)
                $string_format = false;

        $output = array();



        if ($calculate_days === true)
            $output['days'] = floor( $timeleft / (60 * 60 * 24) );

        $remainder =  $timeleft % (60 * 60 * 24);

        if ($calculate_hours === true)
            $output['hours'] = floor( $remainder / (60 * 60) );

        $remainder = $remainder % (60 * 60);

        if ($calculate_minutes === true)
            $output['minutes'] = floor( $remainder / 60);

        if ($calculate_seconds === true)
            $output['seconds'] = $remainder % 60;

        if ($string_format === true)
        {
            $r_days = "";
            $r_hours = "";
            $r_minutes = "";
            $r_seconds = "";

            if ($output['days'] <= 9)
                $r_days = "0" . $output['days'] . ":";
            else if ($output['days'] > 9)
                $r_days = $output['days'] . ":";

            if ($output['hours'] <= 9)
                $r_hours = "0" . $output['hours'] . ":";
            else if ($output['hours'] > 9)
                $r_hours = $output['hours'] . ":";

            if ($output['minutes'] <= 9)
                $r_minutes = "0" . $output['minutes'] . ":";
            else if ($output['minutes'] > 9)
                $r_minutes = $output['minutes'] . ":";

            if ($output['seconds'] <= 9)
                $r_seconds = "0" . $output['seconds'];
            else if ($output['seconds'] > 9)
                $r_seconds = $output['seconds'];

            $remain_st = "";

            if ($calculate_days === true)
                $remain_st .= $r_days;
            if ($calculate_hours === true)
                $remain_st .= $r_hours;
            if ($calculate_minutes === true)
                $remain_st .= $r_minutes;
            if ($calculate_seconds === true)
                $remain_st .= $r_seconds;

            $output['string_format'] = $remain_st;
        }


        return $output;

    }

}

?>