<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\TString;

require_once("TString.php");

/**
 * Helper class to manipulate with Date and Times.
 */
class DateTimes {
    /**
     * Format of date/time in RSS-feeds.
     */
    const RSS_DTS = "ddd, dd MMM yyyy HH:mm:ss zzz";

    /**
     * Get time as Unix timestamp.
     * @param TString $time_string Input string.
     * @return Integer Resulting time (Unix timestamp).
     */
    public static function getTime($time_string= null) {
        if ($time_string == null) $time_string = "now";
        return strtotime($time_string instanceof TString ? $time_string->getValue() : $time_string);
    }

    /**
     * Get Unix timestamp from date/time extracted from RSS-feed.
     * @param TString $time_string Input string.
     * @return Integer Resulting timestamp.
     */
    public static function fromRss($time_string) {
        return self::getTime($time_string);

    }

    /**
     * Format time from Unix timestamp to string presentation.
     * @param TString $format_string Format to apply.
     * @param Integer $time_value Input time value (Unix timestamp).
     * @return TString Resulting string.
     */
    public static function format($format_string, $time_value = 0) {
        return $time_value == 0 ? date($format_string, strtotime("now")) : date($format_string, $time_value);
    }

    /**
     * Format time from timestamp to GMT string presentation.
     * @param TString $format_string Format to apply.
     * @param Integer $time_value Input time value (Unix timestamp).
     * @return TString Resulting string.
     */
    public static function gmtFormat($format_string, $time_value = 0) {
        return $time_value == 0 ? gmdate($format_string, strtotime("now")) : gmdate($format_string, $time_value);
    }
}
