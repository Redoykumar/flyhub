<?php

namespace App\Helpers;

class SimplifyNumber
{
    public static function convertDurationToMinutes($duration)
    {
        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches)) {
            $hours = isset($matches[1]) ? $matches[1] : 0;
            $minutes = isset($matches[2]) ? $matches[2] : 0;

            return ($hours * 60) + $minutes;
        }

        return 0;  // return 0 if the duration is invalid or cannot be parsed
    }
}