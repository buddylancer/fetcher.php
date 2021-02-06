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

require_once("Bula/Meta.php");
require_once("RegexOptions.php");
require_once("TString.php");

/**
 * Helper class for manipulations using Regex.
 */
class Regex
{

    /**
     * Check whether input string matches a pattern.
     * @param TString $input Input string to check.
     * @param TString $pattern Pattern to check.
     * @param Integer $options Matching options (0 - no options).
     * @return Boolean True - matches, False - not matches.
     */
    public static function isMatch($input, $pattern, $options = 0 )
    {
        $inputValue = CAT($input);
        $patternValue = CAT(DIV, $pattern, DIV,
            ((INT($options) & RegexOptions::IgnoreCase) != 0) ? "i" : null);
        $result = preg_match($patternValue, $inputValue);
        if ($result === false)
           return false;
        return $result == 1;
    }

    /**
     * Replace pattern.
     * @param TString $input Input string to process.
     * @param TString $pattern Pattern to replace.
     * @param TString $replacement Replacing string.
     * @param Integer $options Matching options (0 - no options).
     * @return TString Resulting string.
     */
    public static function replace($input, $pattern, $replacement, $options = 0)
    {
        $patternValue = CAT(DIV, $pattern, DIV,
            ((INT($options) & RegexOptions::IgnoreCase) != 0) ?    "i" : null);
        return new TString(preg_replace($patternValue, CAT($replacement), $input->getValue()));
    }

    /**
     * Split a string using pattern.
     * @param TString $input Input string to process.
     * @param TString $pattern Pattern to split by.
     * @param Integer $options Matching options (0 - no options).
     * @return TString[] Resulting array of strings.
     */
    public static function split($input, $pattern, $options = null )
    {
        $patternValue = CAT(DIV, self::escape($pattern), DIV,
            ((INT($options) & RegexOptions::IgnoreCase) != 0) ? "i" : null);
        $pregArray = preg_split($patternValue, $input->getValue(), -1, PREG_SPLIT_NO_EMPTY);
        $outArray = array();
        foreach ($pregArray as $pregItem)
            $outArray[] = new TString($pregItem);
        return $outArray;
    }

    /**
     * Get matching strings.
     * @param TString $input Input string to process.
     * @param TString $pattern Pattern to search for.
     * @param Integer $options Matching options (0 - no options).
     * @return TString[] Resulting array of strings (or null).
     */
    public static function getMatches($input, $pattern, $options = null )
    {
        $patternValue = CAT(DIV, self::escape($pattern), DIV, "u",
            ((INT($options) & RegexOptions::IgnoreCase) != 0) ? "i" : null);
        $outArray = array();
        $result = new ArrayList();
        if (preg_match($patternValue, $input->getValue(), $outArray) > 0)
            $result = new ArrayList($outArray);
        return $result->toArray();
    }

    /**
     * Get quoted string/pattern.
     * @param TString $input Input string/pattern.
     * @return TString Resulting quoted string/pattern.
     */
    public static function escape($pattern)
    {
        return preg_quote(CAT($pattern), DIV);

    }

    /**
     * Get unquoted string.
     * @param TString $input Quoted string.
     * @return TString Resulting unquoted string.
     */
    public static function unescape($input)
    {
        if ($input instanceof TString)
            return new TString(stripslashes($input->getValue()));
        else
            return stripslashes($input);
    }
}
