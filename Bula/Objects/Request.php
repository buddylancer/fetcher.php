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

use Bula\Objects\Arrays;
use Bula\Objects\Enumerator;
use Bula\Objects\Hashtable;
use Bula\Objects\Regex;

require_once("Arrays.php");
require_once("Enumerator.php");
require_once("Hashtable.php");
require_once("Regex.php");

/**
 * Helper class for processing query/form request.
 */
class Request {
    /** Internal storage for GET/POST variables */
    private static $Vars = null;
    /** Internal storage for SERVER variables */
    private static $ServerVars = null;

    /**
     * Initialize internal variables for new request.
     */
    public static function initialize() {
        self::$Vars = Arrays::newHashtable();
        self::$Vars->setPullValues(true);
        self::$ServerVars = Arrays::newHashtable();
        self::$ServerVars->setPullValues(true);
    }

    public static function getPrivateVars() {
        return self::$Vars;
    }

    /**
     * Check whether request contains variable.
     * @param TString $name Variable name.
     * @return Boolean True - variable exists, False - not exists.
     */
    public static function contains($name) {
        return self::$Vars->containsKey($name);
    }

    /**
     * Get variable from internal storage.
     * @param TString $name Variable name.
     * @return TString Variable value.
     */
    public static function get($name) {
        //return /*(TString)*/(self::$Vars->containsKey($name) ? self::$Vars->get($name) : null);
        if (!self::$Vars->containsKey($name))
            return null;
        $value = /*(TString)*/self::$Vars->get($name);
        if (NUL($value))
            $value = "";
        return $value;
    }

    /**
     * Set variable into internal storage.
     * @param TString $name Variable name.
     * @param TString $value Variable value.
     */
    public static function set($name, $value) {
        self::$Vars->put($name, $value);
    }

    /**
     * Get all variable keys from request.
     * @return Enumeration All keys enumeration.
     */
    public static function getKeys() {
        return self::$Vars->keys();
    }

    /** Extract all POST variables into internal variables. */
    public static function extractPostVars() {
        $vars = self::getVars(INPUT_POST);
        self::$Vars = Arrays::mergeHashtable(self::$Vars, $vars);
    }

    /** Extract all SERVER variables into internal storage. */
    public static function extractServerVars() {
        $vars = self::getVars(INPUT_SERVER);
        self::$Vars = Arrays::mergeHashtable(self::$ServerVars, $vars);
    }

    /** Extract all GET and POST variables into internal storage. */
    public static function extractAllVars() {
        $vars = self::getVars(INPUT_GET);
        self::$Vars = Arrays::mergeHashtable(self::$Vars, $vars);
        self::extractPostVars();
    }

    /**
     * Check that referer contains text.
     * @param TString $text Text to check.
     * @return Boolean True - referer contains provided text, False - not contains.
     */
    public static function checkReferer($text) {
        //return true; //TODO
        $http_referer = self::getVar(INPUT_SERVER, "HTTP_REFERER");
        if ($http_referer == null)
            return false;
        return $http_referer->indexOf($text) != -1;
    }

    /**
     * Check that request was originated from test script.
     * @return Boolean True - from test script, False - from ordinary user agent.
     */
    public static function checkTester() {
        $http_tester = self::getVar(INPUT_SERVER, "HTTP_USER_AGENT");
        if ($http_tester == null)
            return false;
        return $http_tester->indexOf("Wget") != -1;
    }

    /**
     * Get required parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return TString Resulting value.
     */
    public static function getRequiredParameter($name) {
        $val = null;
        if (self::contains($name))
            $val = self::get($name);
        else
            STOP(CAT("Parameter '", $name, "' is required!"));
        return $val;
    }

    /**
     * Get optional parameter by name.
     * @param TString $name Parameter name.
     * @return TString Resulting value or null.
     */
    public static function getOptionalParameter($name) {
        $val = null;
        if (self::contains($name))
            $val = self::get($name);
        return $val;
    }

    /**
     * Get required integer parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return Integer Resulting value.
     */
    public static function getRequiredInteger($name) {
        $str = self::getRequiredParameter($name);
        if ($str == "" || !self::isInteger($str))
            STOP(CAT("Error in parameter '", $name, "'!"));
        return INT($str);
    }

    /**
     * Get optional integer parameter by name.
     * @param TString $name Parameter name.
     * @return Integer Resulting value or null.
     */
    public static function getOptionalInteger($name) {
        $val = self::getOptionalParameter($name);
        if ($val == null)
            return null;

        $str = STR($val);
        if ($str == "" || !self::isInteger($str))
            STOP(CAT("Error in parameter '", $name, "'!"));
        return INT($val);
    }

    /**
     * Get required string parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return TString Resulting value.
     */
    public static function getRequiredString($name) {
        $val = self::getRequiredParameter($name);
        return $val;
    }

    /**
     * Get optional string parameter by name.
     * @param TString $name Parameter name.
     * @return TString Resulting value or null.
     */
    public static function getOptionalString($name) {
        $val = self::getOptionalParameter($name);
        return $val;
    }

    /**
     * Test (match) a page request with array of allowed pages.
     * @param Object[] $pages Array of allowed pages (and their parameters).
     * @param TString $default_page Default page to use for testing.
     * @return Hashtable Resulting page parameters.
     */
    public static function testPage($pages, $default_page = null) {
        $page_info = new Hashtable();

        // Get page name
        $page = null;
        $page_info->put("from_get", 0);
        $page_info->put("from_post", 0);
        $p_value = self::getVar(INPUT_GET, "p");
        if ($p_value != null) {
            $page = $p_value;
            $page_info->put("from_get", 1);
        }
        $p_value = self::getVar(INPUT_POST, "p");
        if ($p_value != null) {
            $page = $p_value;
            $page_info->put("from_post", 1);
        }
        if ($page == null)
            $page = $default_page;

        $page_info->remove("page");
        for ($n = 0; $n < SIZE($pages); $n += 4) {
            if (EQ($pages[$n], $page)) {
                $page_info->put("page", $pages[$n+0]);
                $page_info->put("class", $pages[$n+1]);
                $page_info->put("post_required", $pages[$n+2]);
                $page_info->put("code_required", $pages[$n+3]);
                break;
            }
        }
        return $page_info;
    }

    /**
     * Check whether text is ordinary name.
     * @param TString $input Input text.
     * @return Boolean True - text matches name, False - not matches.
     */
    public static function isName($input) {
        return Regex::isMatch($input, "^[A-Za-z_]+[A-Za-z0-9_]*$");
    }

    /**
     * Check whether text is domain name.
     * @param TString $input Input text.
     * @return Boolean True - text matches domain name, False - not matches.
     */
    public static function isDomainName($input) {
        return Regex::isMatch($input, "^[A-Za-z]+[A-Za-z0-9\.]*$");
    }

    /**
     * Check whether text is positive integer.
     * @param TString $input Input text.
     * @return Boolean True - text matches, False - not matches.
     */
    public static function isInteger($input) {
        return Regex::isMatch($input, "^[1-9]+[0-9]*$");
    }

    /**
     * Get all variables of given type.
     * @param Integer $type Required type.
     * @return Hashtable Requested variables.
     */

    public static function getVars($type) {
        $output = Arrays::newHashtable();
        $vars = filter_input_array($type);
        if ($vars === false || $vars == null)
            return $output;
        foreach ($vars as $key => $value)
            $output->put($key, $value == null ? "" : $value);
        return $output;
    }

    /**
     * Get a single variable of given type.
     * @param Integer $type Required type.
     * @param TString $name Variable name.
     * @return TString Requested variable.
     */

    public static function getVar($type, $name) {
        $var = filter_input($type, $name);
        return $var == null ? null : new TString($var);
    }

}
Request::initialize();
