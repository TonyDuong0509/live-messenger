<?php

/** Calculate human readable time */
if (!function_exists('timeAgo')) {
    function timeAgo($timestamps)
    {
        $timeDifference = time() - strtotime($timestamps);
        $seconds = $timeDifference;
        $minutes = round($timeDifference / 60);
        $hours = round($timeDifference / 3600);
        $days = round($timeDifference / 86400);

        if ($seconds <= 60) {
            if ($seconds <= 1) {
                return '1 giây trước';
            }
            return $seconds . " giây trước";
        } elseif ($minutes <= 60) {
            return $minutes . " phút trước";
        } elseif ($hours <= 24) {
            return $hours . " tiếng trước";
        } else {
            return date('j M y', strtotime($timestamps));
        }
    }
}
