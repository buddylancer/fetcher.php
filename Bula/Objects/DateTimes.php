<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\TString;

require_once("TString.php");

/**
 * Helper class to manipulate with Date and Times.
 */
class DateTimes
{
    /** Date/time format for processing custom date/times */
    const DTS = "d-M-Y H:i";
    /** Date/time format for processing GMT date/times */
    const GMT_DTS = "d-M-Y H:i \G\M\T";
    /** Date/time format for RSS operations */
    const XML_DTS = "D, d M Y H:i:s \G\M\T";
    /** Date/time format for DB operations */
    const SQL_DTS = "Y-m-d H:i:s";
    /** Format of log-file name. */
    const LOG_DTS = "Y-m-d_H-i-s";
    /** Format of date/time in RSS-feeds. */
    const RSS_DTS = "ddd, dd MMM yyyy HH:mm:ss zzz";

    /**
     * Get time as Unix timestamp.
     * @param TString $timeString Input string.
     * @return Integer Resulting time (Unix timestamp).
     */
    public static function getTime($timeString= null)
    {
        if ($timeString == null) {
            $timeString = "now";
        }
        return strtotime($timeString instanceof TString ? $timeString->getValue() : $timeString);
    }

    /**
     * Get Unix timestamp from date/time extracted from RSS-feed.
     * @param TString $timeString Input string.
     * @return Integer Resulting timestamp.
     */
    public static function fromRss($timeString)
    {
        return self::getTime($timeString);

    }

    /**
     * Format time from Unix timestamp to string presentation.
     * @param TString $formatString Format to apply.
     * @param Integer $timeValue Input time value (Unix timestamp).
     * @return TString Resulting string.
     */
    public static function format($formatString, $timeValue = 0)
    {
        return $timeValue == 0 ? date($formatString, strtotime("now")) : date($formatString, $timeValue);
    }

    /**
     * Format time from timestamp to GMT string presentation.
     * @param TString $formatString Format to apply.
     * @param Integer $timeValue Input time value (Unix timestamp).
     * @return TString Resulting string.
     */
    public static function gmtFormat($formatString, $timeValue = 0)
    {
        return $timeValue == 0 ? gmdate($formatString, strtotime("now")) : gmdate($formatString, $timeValue);
    }

    public static function parse($formatString, $timeString)
    {
        return strtotime(date($formatString, strtotime($timeString)));
    }
}
