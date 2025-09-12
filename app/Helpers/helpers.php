<?php

if (!function_exists('formatToHms')) {
    /**
     * Formats a duration in seconds into HH:MM:SS or HH:MM format.
     *
     * @param int $seconds
     * @param bool $showSeconds
     * @return string
     */
    function formatToHms(int $seconds, bool $showSeconds = false): string
    {
        if ($seconds <= 0) {
            return $showSeconds ? '00:00:00' : '00:00';
        }

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        if ($showSeconds) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $h, $m);
    }
}