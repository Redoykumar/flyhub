<?php
namespace Redoy\FlyHub\Helpers;

/**
 * Utility functions for simplifying and formatting numbers and related data.
 */
class SimplifyNumber
{
    /**
     * Converts an ISO 8601 duration string to minutes.
     *
     * @param string $duration Duration in ISO 8601 format (e.g., PT2H30M)
     * @return int Duration in minutes
     */
    public static function airlineImageUrl(string $carrier = null): string
    {
        $code = $carrier ?? 'Unknown';
        return 'https://images.kiwi.com/airlines/64/' . $code . '.png';
    }
    public static function convertDurationToMinutes(string $duration): int
    {
        if (preg_match('/PT(\d+)H(\d+)?M?/', $duration, $matches)) {
            $hours = (int) ($matches[1] ?? 0);
            $minutes = (int) ($matches[2] ?? 0);
            return ($hours * 60) + $minutes;
        }
        return 0;
    }
    public static function extractDate(string $datetime): string
    {
        try {
            $dt = new \DateTime($datetime);
            return $dt->format('Y-m-d');  // '2025-05-16'
        } catch (\Exception $e) {
            return '';  // Return empty if there is an error
        }
    }

    public static function extractTime(string $datetime): string
    {
        try {
            $dt = new \DateTime($datetime);
            return $dt->format('H:i:s');  // '18:50:00'
        } catch (\Exception $e) {
            return '';  // Return empty if there is an error
        }
    }




    // Add other existing helper methods here (if any)
}