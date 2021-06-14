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

use Bula\Objects\Arrays;
use Bula\Objects\Enumerator;
use Bula\Objects\Hashtable;
use Bula\Objects\Regex;

require_once("RequestBase.php");
require_once("Arrays.php");
require_once("Enumerator.php");
require_once("Hashtable.php");
require_once("Regex.php");

/**
 * Helper class for processing query/form request.
 */
class Request extends RequestBase
{
    /** Internal storage for GET/POST variables */
    private $Vars = null;
    /** Internal storage for SERVER variables */
    private $ServerVars = null;

    public function __construct($currentRequest)
    { $this->initialize(); }

    /** Initialize internal variables for new request. */
    private function initialize()
    {
        $this->Vars = Arrays::newHashtable();
        $this->Vars->setPullValues(true);
        $this->ServerVars = Arrays::newHashtable();
        $this->ServerVars->setPullValues(true);
    }

    /**
     * Get private variables.
     * @return Hashtable
     */
    public function getPrivateVars()
    {
        return $this->Vars;
    }

    /**
     * Check whether request contains variable.
     * @param TString $name Variable name.
     * @return Boolean True - variable exists, False - not exists.
     */
    public function contains($name)
    {
        return $this->Vars->containsKey($name);
    }

    /**
     * Get variable from internal storage.
     * @param TString $name Variable name.
     * @return TString Variable value.
     */
    public function get($name)
    {
        //return (self::$Vars->containsKey($name) ? self::$Vars->get($name) : null);
        if (!$this->Vars->containsKey($name))
            return null;
        $value = $this->Vars->get($name);
        if (NUL($value))
            $value = "";
        return $value;
    }

    /**
     * Set variable into internal storage.
     * @param TString $name Variable name.
     * @param TString $value Variable value.
     */
    public function set($name, $value)
    {
        $this->Vars->put($name, $value);
    }

    /**
     * Get all variable keys from request.
     * @return Enumeration All keys enumeration.
     */
    public function getKeys()
    {
        return $this->Vars->keys();
    }

    /** Extract all POST variables into internal variables. */
    public function extractPostVars()
    {
        $vars = $this->getVars(INPUT_POST);
        $this->Vars = Arrays::mergeHashtable($this->Vars, $vars);
    }

    /** Extract all SERVER variables into internal storage. */
    public function extractServerVars()
    {
        $vars = $this->getVars(INPUT_SERVER);
        $this->Vars = Arrays::mergeHashtable($this->ServerVars, $vars);
    }

    /** Extract all GET and POST variables into internal storage. */
    public function extractAllVars()
    {
        $vars = $this->getVars(INPUT_GET);
        $this->Vars = Arrays::mergeHashtable($this->Vars, $vars);
        $this->extractPostVars();
    }

    /**
     * Check that referer contains text.
     * @param TString $text Text to check.
     * @return Boolean True - referer contains provided text, False - not contains.
     */
    public function checkReferer($text)
    {
        //return true; //TODO
        $httpReferer = $this->getVar(INPUT_SERVER, "HTTP_REFERER");
        if ($httpReferer == null)
            return false;
        return $httpReferer->indexOf($text) != -1;
    }

    /**
     * Check that request was originated from test script.
     * @return Boolean True - from test script, False - from ordinary user agent.
     */
    public function checkTester()
    {
        $httpTester = $this->getVar(INPUT_SERVER, "HTTP_USER_AGENT");
        if ($httpTester == null)
            return false;
        return $httpTester->indexOf("Wget") != -1;
    }

    /**
     * Get required parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return TString Resulting value.
     */
    public function getRequiredParameter($name)
    {
        $val = null;
        if ($this->contains($name))
            $val = $this->get($name);
        else {
            $error = CAT("Parameter '", $name, "' is required!");
            $this->response->end($error);
        }
        return $val;
    }

    /**
     * Get optional parameter by name.
     * @param TString $name Parameter name.
     * @return TString Resulting value or null.
     */
    public function getOptionalParameter($name)
    {
        $val = null;
        if ($this->contains($name))
            $val = $this->get($name);
        return $val;
    }

    /**
     * Get required integer parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return Integer Resulting value.
     */
    public function getRequiredInteger($name)
    {
        $str = $this->getRequiredParameter($name);
        if ($str == "" || !self::isInteger($str)) {
            $error = CAT("Error in parameter '", $name, "'!");
            $this->response->end($error);
        }
        return INT($str);
    }

    /**
     * Get optional integer parameter by name.
     * @param TString $name Parameter name.
     * @return Integer Resulting value or null.
     */
    public function getOptionalInteger($name)
    {
        $val = $this->getOptionalParameter($name);
        if ($val == null)
            return null;

        $str = STR($val);
        if ($str == "" || !self::isInteger($str)) {
            $error = CAT("Error in parameter '", $name, "'!");
            $this->response->end($error);
        }
        return INT($val);
    }

    /**
     * Get required string parameter by name (or stop execution).
     * @param TString $name Parameter name.
     * @return TString Resulting value.
     */
    public function getRequiredString($name)
    {
        $val = $this->getRequiredParameter($name);
        return $val;
    }

    /**
     * Get optional string parameter by name.
     * @param TString $name Parameter name.
     * @return TString Resulting value or null.
     */
    public function getOptionalString($name)
    {
        $val = $this->getOptionalParameter($name);
        return $val;
    }

    /**
     * Test (match) a page request with array of allowed pages.
     * @param Object[] $pages Array of allowed pages (and their parameters).
     * @param TString $defaultPage Default page to use for testing.
     * @return Hashtable Resulting page parameters.
     */
    public function testPage($pages, $defaultPage = null)
    {
        $pageInfo = new Hashtable();

        // Get page name
        $page = null;
        $pageInfo->put("from_get", 0);
        $pageInfo->put("from_post", 0);

        $apiValue = $this->getVar(INPUT_GET, "api");
        if ($apiValue != null) {
            if (EQ($apiValue, "rest")) // Only Rest for now
                $pageInfo->put("api", $apiValue);
        }

        $pValue = $this->getVar(INPUT_GET, "p");
        if ($pValue != null) {
            $page = $pValue;
            $pageInfo->put("from_get", 1);
        }
        $pValue = $this->getVar(INPUT_POST, "p");
        if ($pValue != null) {
            $page = $pValue;
            $pageInfo->put("from_post", 1);
        }
        if ($page == null)
            $page = $defaultPage;

        $pageInfo->remove("page");
        for ($n = 0; $n < SIZE($pages); $n += 4) {
            if (EQ($pages[$n], $page)) {
                $pageInfo->put("page", $pages[$n + 0]);
                $pageInfo->put("class", $pages[$n + 1]);
                $pageInfo->put("post_required", $pages[$n + 2]);
                $pageInfo->put("code_required", $pages[$n + 3]);
                break;
            }
        }
        return $pageInfo;
    }

    /**
     * Check whether text is ordinary name.
     * @param TString $input Input text.
     * @return Boolean True - text matches name, False - not matches.
     */
    public static function isName($input)
    {
        return Regex::isMatch($input, "^[A-Za-z_]+[A-Za-z0-9_]*$");
    }

    /**
     * Check whether text is domain name.
     * @param TString $input Input text.
     * @return Boolean True - text matches domain name, False - not matches.
     */
    public static function isDomainName($input)
    {
        return Regex::isMatch($input, "^[A-Za-z]+[A-Za-z0-9\.]*$");
    }

    /**
     * Check whether text is positive integer.
     * @param TString $input Input text.
     * @return Boolean True - text matches, False - not matches.
     */
    public static function isInteger($input)
    {
        return Regex::isMatch($input, "^[1-9]+[0-9]*$");
    }
}
