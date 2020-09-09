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

use Bula\Objects\ArrayList;
use Bula\Objects\TString;

require_once("TString.php");

/**
 * Helper class for manipulations with strings.
 */
class Strings
{
    /**
     * Provide empty array.
     * @return TString[] Empty array of strings.
     */
    public static function emptyArray()
    {
        return array();
    }

	/**
     * Convert first char of a string to upper case.
     * @param TString $input Input string.
     * @return TString Resulting string.
     */
    public static function firstCharToUpper($input)
    {
        $input = $input instanceof TString ? $input : new TString($input);
        return self::concat($input->substring(0, 1)->toUpperCase(), $input->substring(1));
	}

	/**
     * Join an array of strings using divider,
     * @param TString $divider Divider (yes, may be empty).
     * @param TString[] $strings Array of strings.
     * @return TString Resulting string.
     */
    public static function join($divider, $strings)
    {
        $output = new TString();
        $count = 0;
        foreach ($strings as $string1) {
            if ($count > 0)
                $output->concat($divider);
            $output->concat($string1);
            $count++;
        }
        return $output;
	}

    /**
     * Remove HTML tags from string except allowed ones.
     * @param TString $input Input string.
     * @param TString $except List of allowed tags (do not remove).
     * @return TString Resulting string.
     */
    public static function removeTags($input, $except= null )
    {
        if ($except != null && $except instanceof TString) $except = $except->getValue();
        return new TString(strip_tags($input->getValue(), $except == null ? null : $except));
	}

	/**
     * Add slashes to the string.
     * @param TString $input Input string.
     * @return TString Resulting string.
     */
    public static function addSlashes($input)
    {
		return new TString(addslashes(CAT($input)));
	}

	/**
     * remove slashes from the string.
     * @param TString $input Input string.
     * @return TString Resulting string.
     */
	public static function stripSlashes($input)
    {
		return new TString(stripslashes(CAT($input)));
	}

	/**
     * Count substrings in the string.
     * @param TString $input Input string.
     * @param TString $chunk String to count.
     * @return Integer Number of substrings.
     */
    public static function countSubstrings($input, $chunk)
    {
		if ($input->length() == 0)
			return 0;
		$replaced = $input->replace($chunk, "");
		return $input->length() - $replaced->length();
	}

	/**
     * Concatenate a number of strings to a single one.
     * @param Object[] $args Array of strings.
     * @return TString Resulting string.
     */
    public static function concat(/*...*/)
    {
		$output = new TString();
		$args = func_get_args();
		if (SIZE($args) != 0) {
			foreach ($args as $arg) {
                if ($arg == null)
                    continue;
				$output = $output->concat($arg);
			}
		}
		return $output;
	}

	/**
     * Split a string using divider/separator.
     * @param TString $divider Divider/separator.
     * @param TString $input Input string.
     * @return TString[] Array of resulting strings.
     */
    public static function split($divider, $input)
    {
		$divider = CAT(DIV, Regex::escape($divider), DIV);
        if ($input instanceof TString) $input = $input->getValue();
		$chunks =
            preg_split($divider, $input, -1, PREG_SPLIT_NO_EMPTY);
		$result = new ArrayList();
        for ($n = 0; $n < SIZE($chunks); $n++)
			$result->add($chunks[$n]);
		return $result->toArray();
	}

	/**
     * Replace a number of substring(s) from a string.
     * @param TString $from Substring to replace.
     * @param TString $to Replacement string.
     * @param TString $input Input string.
     * @param type $limit Max number of replacements [optional].
     * @return TString Resulting string.
     */
    public static function replace($from, $to, $input, $limit= 0)
    {
//if php
        $isObject = $input instanceof TString;
        if (!$isObject) $input = new TString($input);
        if (!$from instanceof TString) $from = new TString($from);
        if (!$to instanceof TString) $to = new TString($to);
		$hasPattern = $from->length() > 1 && $from->startsWith(DIV) && $from->endsWith(DIV);
        $result = null;
        if ($limit != 0 || $hasPattern) {
            // Use preg_replace
            if (!$hasPattern) $from = self::concat(DIV, $from, DIV);
			if ($limit == 0)
				$result = preg_replace($from->getValue(), $to->getValue(), $input->getValue());
            else
				$result = preg_replace($from->getValue(), $to->getValue(), $input->getValue(), $limit);
        }
        else {
            // Use str_replace
			$result = str_replace($from->getValue(), $to->getValue(), $input->getValue());
        }
		return $isObject ? new TString($result) : $result;
	}

    /**
     * Replace all substrings using regular expressions.
     * @param TString $regex Regular expression to match substring(s).
     * @param TString $to Replacement string.
     * @param TString $input Input string.
     * @return TString Resulting string.
     */
    public static function replaceAll($regex, $to, $input)
    {
        $regex = CAT(DIV, $regex, DIV);
        return self::replace($regex, $to, $input);
    }

    /**
     * Replace first substring using regular expressions.
     * @param TString $regex Regular expression to match substring.
     * @param TString $to Replacement string.
     * @param TString $input Input string.
     * @return TString Resulting string.
     */
    public static function replaceFirst($regex , $to, $input)
    {
        $regex = CAT(DIV, $regex, DIV);
        return self::replace($regex, $to, $input, 1);
    }

    /**
     * Replace "keys by values" in a string.
     * @param TString $template Input template.
     * @param Hashtable $hash Set of key/value pairs.
     * @return TString Resulting string.
     */
    public static function replaceInTemplate($template, $hash)
    {
        return $content = strtr($template, Arrays::toArray($hash));

    }

}
