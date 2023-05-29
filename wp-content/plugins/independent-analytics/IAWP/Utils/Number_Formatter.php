<?php

namespace IAWP_SCOPED\IAWP\Utils;

use DateTime;
class Number_Formatter
{
    /**
     * Pass in 90 and get back 1:30. Pass in 121 and get back 2:01.
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function second_to_minute_timestamp(int $seconds) : string
    {
        $unix_epoch = new DateTime("@0");
        $now = new DateTime("@{$seconds}");
        $interval = $unix_epoch->diff($now);
        return $interval->format('%i:%S');
    }
    public static function format($number, $format = 'decimal', $decimals = 0)
    {
        if ($format == 'percent') {
            if (\class_exists('\\NumberFormatter')) {
                $formatter = new \NumberFormatter(\get_locale(), \NumberFormatter::PERCENT);
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
                return $formatter->format($number / 100);
            } else {
                return \number_format_i18n($number, $decimals) . '%';
            }
        } else {
            return \number_format_i18n($number, $decimals);
        }
    }
}
