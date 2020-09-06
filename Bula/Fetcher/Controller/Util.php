<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Fetcher\Config;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

use Bula\Objects\Request;
use Bula\Objects\DateTimes;
use Bula\Objects\Helper;
use Bula\Objects\TString;
use Bula\Objects\Strings;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");

/**
 * Various helper methods.
 */
class Util {
    /**
     * Output text safely.
     * @param TString $input Text to output.
     * @return TString Converted text.
     */
    public static function safe($input) {
        $output = Strings::stripSlashes($input);
        $output = $output->replace("<", "&lt;");
        $output = $output->replace(">", "&gt;");
        $output = $output->replace("\"", "&quot;");
        return $output;
    }

    /**
     * Output text safely with line breaks.
     * @param TString $input Text to output.
     * @return TString Converted text.
     */
    public static function show($input) {
        if ($input == null)
            return null;
        $output = self::safe($input);
        $output = $output->replace("\n", "<br/>");
        return $output;
    }

    /**
     * Format date/time to GMT presentation.
     * @param TString $input Input date/time.
     * @return TString Resulting date/time.
     */
    public static function showTime($input) {
        return DateTimes::format(Config::GMT_DTS, DateTimes::getTime($input));
    }

    /**
     * Format string.
     * @param TString $format Format (template).
     * @param Object[] $arr Parameters.
     * @return TString Resulting string.
     */
    public static function formatString($format, $arr) {
        if (BLANK($format))
            return null;
        $output = $format;
        $arr_size = SIZE($arr);
        for ($n = 0; $n < $arr_size; $n++) {
            $match = CAT("{", $n, "}");
            $ind = $format->indexOf($match);
            if ($ind == -1)
                continue;
            $output = $output->replace($match, /*(TString)*/$arr[$n]);
        }
        return $output;
    }

    /**
     * Main logic for getting/saving page from/into cache.
     * @param Engine $engine Engine instance.
     * @param TString $cache_folder Cache folder root.
     * @param TString $page_name Page to process.
     * @param TString $class_name Appropriate class name.
     * @param TString $query Query to process.
     * @return TString Resulting content.
     */
    public static function showFromCache($engine, $cache_folder, $page_name, $class_name, $query = null) {
        if (EQ($page_name, "bottom"))
            $query = $page_name;
        else {
            if ($query == null)
                $query = Request::getVar(INPUT_SERVER, "QUERY_STRING");
            if (BLANK($query))
                $query = "p=home";
        }

        $content = null;

        if (EQ($page_name, "view_item")) {
            $title_pos = $query->indexOf("&title=");
            if ($title_pos != -1)
                $query = $query->substring(0, $title_pos);
        }

        $hash = $query;
        //$hash = str_replace("?", "_Q_", $hash);
        $hash = Strings::replace("=", "_EQ_", $hash);
        $hash = Strings::replace("&", "_AND_", $hash);
        $file_name = Strings::concat($cache_folder, "/", $hash, ".cache");
        if (Helper::fileExists($file_name)) {
            $content = Helper::readAllText($file_name);
            //$content = CAT("*** Got from cache ", str_replace("/", " /", $file_name), "***<br/>", $content);
        }
        else {
            $prefix = EQ($page_name, "bottom") ? null : "Pages/";
            $content = $engine->includeTemplate(CAT("Bula/Fetcher/Controller/", $prefix, $class_name));

            Helper::testFileFolder($file_name);
            Helper::writeText($file_name, $content);
            //$content = CAT("*** Cached to ", str_replace("/", " /", $file_name), "***<br/>", $content);
        }
        return $content;
    }

    /**
     * Max length to extract from string.
     */
    const MAX_EXTRACT = 100;

    /**
     * Extract info from a string.
     * @param TString $source Input string.
     * @param TString $after Substring to extract info "After".
     * @param TString $to Substring to extract info "To".
     * @return TString Resulting string.
     */
    public static function extractInfo($source, $after, $to = null) {
        $result = null;
        if (!NUL($source)) {
            $index1 = 0;
            if (!NUL($after)) {
                $index1 = $source->indexOf($after);
                if ($index1 == -1)
                    return null;
                $index1 += LEN($after);
            }
            $index2 = $source->length();
            if (!NUL($to)) {
                $index2 = $source->indexOf($to, $index1);
                if ($index2 == -1)
                    $index2 = $source->length();
            }
            $length = $index2 - $index1;
            if ($length > self::MAX_EXTRACT)
                $length = self::MAX_EXTRACT;
            $result = $source->substring($index1, $length);
        }
        return $result;
    }

    /**
     * Remove some content from a string.
     * @param TString $source Input string.
     * @param TString $from Substring to remove "From".
     * @param TString $to Substring to remove "To".
     * @return TString Resulting string.
     */
    public static function removeInfo($source, $from, $to = null) {
        $result = null;
        $index1 = $from == null ? 0 : $source->indexOf($from);
        if ($index1 != -1) {
            if ($to == null)
                $result = $source->substring($index1);
            else {
                $index2 = $source->indexOf($to, $index1);
                if ($index2 == -1)
                    $result = $source;
                else {
                    $index2 += $to->length();
                    $result = Strings::concat(
                        $source->substring(0, $index1),
                        $source->substring($index2));
                }
            }
        }
        return $result->trim();
    }

    private static $ru_chars =
    array(
        "а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п",
        "р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я",
        "А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П",
        "Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я",
        "á", "ą", "ä", "ę", "ó", "ś",
        "Á", "Ą", "Ä", "Ę", "Ó", "Ś"
    );

    private static $en_chars =
    array(
        "a","b","v","g","d","e","io","zh","z","i","y","k","l","m","n","o","p",
        "r","s","t","u","f","h","ts","ch","sh","shch","\"","i","\"","e","yu","ya",
        "A","B","V","G","D","E","IO","ZH","Z","I","Y","K","L","M","N","O","P",
        "R","S","T","U","F","H","TS","CH","SH","SHCH","\"","I","\"","E","YU","YA",
        "a", "a", "ae", "e", "o", "s",
        "A", "a", "AE", "E", "O", "S"
    );

    /**
     * Transliterate Russian text.
     * @param TString $ru_text Original Russian text.
     * @return TString Transliterated text.
     */
    public static function transliterateRusToLat($ru_text) {
        return new TString(str_replace(self::$ru_chars, self::$en_chars, $ru_text->getValue()));

    }
}
