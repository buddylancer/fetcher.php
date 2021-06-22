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

require_once("Bula/Meta.php");

/**
 * This is straight-forward emulation of java String class.
 */
class TString
{
    private $value = "";
    private $isUtf = false;
    public $Utf = "";

    public function __construct($str = null)
    {
        $this->initialize($str);
    }

    public function set($str)
    {
        $this->initialize($str);
    }

    private function initialize($str)
    {
        if ($str == null) {
            $this->value = "";
            return;
        }
        if ($str instanceof TString)
            $this->value = $str->value;
        else
            $this->value = CAT($str);

        $this->isUtf = (mb_detect_encoding($this->value) == "UTF-8"); //TODO
        if ($this->isUtf) {
            $this->Utf = "u";
            $this->value = mb_convert_encoding($this->value, "UTF-8", "UTF-8");
        }
        //$this->isUtf = (preg_match("//u", $this->value) == 1);
    }

    /**
     * Get internal value.
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get string length.
     * @return Integer
     */
    public function length()
    {
        return $this->isUtf ? mb_strlen($this->value) : strlen($this->value);
    }

    /**
     * Get first index of a substring.
     * @param TString $input Substring to search for.
     * @param type $offset Offset from beginning of a string [optional].
     * @return Integer Index found (or -1 if not found)
     */
    public function indexOf($input, $offset = 0)
    {
        $str = $input instanceof TString ? $input->value : $input;
        $pos = $this->isUtf ?
            ($offset == null ? mb_strpos($this->value, $str) : mb_strpos($this->value, $str, $offset)) :
            ($offset == null ? strpos($this->value, $str) : strpos($this->value, $str, $offset));
        return $pos !== false ? $pos : -1;
    }

    /**
     * Get last index of a substring.
     * @param TString $input Substring to search for.
     * @return Integer Index found (or -1 if not found)
     */
    public function lastIndexOf($input)
    {
        $str = $input instanceof TString ? $input->value : $input;
        $pos = $this->isUtf ? mb_strrpos($this->value, $str) : strrpos($this->value, $str);
        return $pos !== false ? $pos : -1;
    }

    /**
     * Get a char in a position.
     * @param Integer $n Position of a char.
     * @return TString Resulting 1-char string.
     */
    public function charAt($n)
    {
        return $this->isUtf ? mb_substr($this->value, $n, 1) : substr($this->value, $n, 1);
    }

    /**
     * Concatenate a string to the end of this string.
     * @param TString $input TString to concatenate/append.
     * @return TString This string.
     */
    public function concat($input)
    {
        if ($input == null)
            return $this;
        $newValue = CAT($this->value, $input);
        $this->initialize($newValue);
        return $this;
    }

    /**
     * Check whether this string equals to a string.
     * @param TString $input TString to compare.
     * @return Boolean True - are equal, False - are not equal.
     */
    public function equals($input)
    {
        if ($input == null)
            return false;
        return EQ($this->value, $input);
    }

    /**
     * Check whether this string contains another string.
     * @param TString $input TString to check for.
     * @return Boolean True - this string contains another string, False - no.
     */
    public function contains($input)
    {
        if ($input == null)
            return false;
        $str = $input instanceof TString ? $input->value : $input;
        //if (strlen($str) > $this->length())
        //    return false;
        if ($this->indexOf($str) != -1)
            return true;
        return false;
    }

    /**
     * Check whether this string is empty.
     * @return Boolean
     */
    public function isEmpty()
    {
        return $this->value == null || $this->value == "";
    }

    public static function compare($str1, TString $str2)
    {
        //TODO
    }

    /**
     * Check whether this string starts with another string.
     * @param TString $input TString to check for.
     * @return Boolean
     */
    public function startsWith($input)
    {
        if ($input == null)
            return false;
        $str = $input instanceof TString ? $input->value : $input;
        if ($this->indexOf($str) == 0)
            return true;
        return false;
    }

    /**
     * Check whether this string ends with another string.
     * @param TString $input TString to check for.
     * @return Boolean
     */
    public function endsWith($input)
    {
        if ($input == null)
            return false;
        if ($input instanceof TString) $input = $input->value;
        $pos = $this->lastIndexOf($input);
        if ($pos == -1)
            return false;
        if ($pos + LEN($input) == $this->length())
            return true;
        return false;
    }

    /**
     * Replace substring with another string.
     * @param TString $from TString to replace.
     * @param TString $to Replacement string.
     * @return TString Resulting string.
     */
    public function replace($from, $to)
    {
        return $this->privateReplace($from, $to);
    }

    /**
     * Replace all substrings with another string using regular expressions.
     * @param TString $regex Regular expression to match substring(s).
     * @param TString $to Replacement string.
     * @return TString Resulting string.
     */
    public function replaceAll($regex, $to)
    {
        return $this->privateReplace(CAT(DIV, $regex, DIV, $this->Utf), $to);
    }

    /**
     * Replace first substring with another string using regular expressions.
     * @param TString $regex Regular expression to match substring.
     * @param TString $to Replacement string.
     * @return TString Resulting string.
     */
    public function replaceFirst($regex , $to)
    {
        return $this->privateReplace(CAT(DIV, $regex, DIV, $this->Utf), $to, 1);
    }

    /**
     * Internal replacing logic.
     * @param TString $from Substring to replace.
     * @param TString $to Replacement string.
     * @param type $limit Max number of replacements [optional].
     * @return TString Resulting string.
     */
    private function privateReplace($from, $to, $limit = 0)
    {
        $fromValue = CAT($from);
        $toValue = CAT($to);
        $hasPattern = strpos($fromValue, DIV) === 0 &&
            strrpos($fromValue, DIV, 1) === (strlen($fromValue) - 1);
        if ($limit != 0 || $hasPattern) {
            // Use preg_replace
            if (!$hasPattern)
                $fromValue = CAT(DIV, $fromValue, DIV, $this->Utf);
            $result = $limit == 0 ?
                preg_replace($fromValue, $toValue, $this->value) :
                preg_replace($fromValue, $toValue, $this->value, $limit);
        }
        else {
            // Use str_replace
            $result = str_replace($fromValue, $toValue, $this->value);
        }
        return new TString($result);
    }

    /**
     * Remove substring from a position of this string.
     * @param Integer $start Char position/index to remove from.
     * @param Integer $length Length to remove [optional].
     * @return TString Resulting string.
     */
    public function remove($start, $length = 0)
    {
        $count = $length == 0 ? 0 : INT($length);
        if ($start < 0 || $count < 0 || $start + $count >= $this->length())
            return null;
        if ($count == 0)
            return new TString(substr($this->value, 0, $start));
        return CAT(substr($this->value, 0, $start), substr($this->value, $start + $count));
    }

    /**
     * Get substring of this string.
     * @param type $start Char position/index to get substring from.
     * @param type $length Length of substring [optional].
     * @return TString Resulting string.
     */
    public function substring($start, $length = 0)
    {
        $line = $this->isUtf ?
            ($length == 0 ? mb_substr($this->value, $start) : mb_substr($this->value, $start, $length)) :
            ($length == 0 ? substr($this->value, $start) : substr($this->value, $start, $length));
        return new TString($line);
    }

    /**
     * Trim this string.
     * @param TString $chars Which chars to trim [optional].
     * @return TString Resulting string.
     */
    public function trim($chars = null)
    {
        $line = $chars == null ? trim($this->value) : trim($this->value, $chars);
        return new TString($line);
    }

    public function trimEnd($chars = null)
    {
        //TODO
    }

    public function trimStart($chars = null)
    {
        //TODO
    }

    /**
     * Make a copy of this string.
     * @return TString Resulting string.
     */
    public function copy()
    {
        return new TString($this->value);
    }

    /**
     * Split this string using divider.
     * @param TString $divider TString to split over.
     * @param type $count Max number of chunks [optional].
     * @return TString[] Resulting array of strings.
     */
    public function split($divider, $count = -1)
    {
        $pattern = CAT(DIV, $divider, DIV, $this->Utf);
        $chunks = preg_split($pattern, $this->value, $count, PREG_SPLIT_NO_EMPTY);
        $result = array();
        foreach ($chunks as $chunk)
            $result[] = new TString ($chunk);
        return $result;
    }

    /**
     * Convert this string to upper case.
     * @return TString Resulting string.
     */
    public function toUpperCase()
    {
        return new TString($this->isUtf ? mb_strtoupper($this->value) : strtoupper($this->value));
    }

    /**
     * Convert this string to lower case.
     * @return TString Resulting string.
     */
    public function toLowerCase()
    {
        return new TString($this->isUtf ? mb_strtolower($this->value) : strtolower($this->value));
    }
}
