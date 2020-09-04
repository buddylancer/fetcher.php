<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Testing;

use Bula\Fetcher\Config;
use Bula\Fetcher\Controller\Page;
use Bula\Objects\ArrayList;
use Bula\Objects\TString;
use Bula\Objects\Strings;
use Bula\Objects\Request;
use Bula\Objects\Response;
use Bula\Model\DataSet;

require_once("Bula/Meta.php");
require_once("Bula/Fetcher/Controller/Page.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Response.php");
require_once("Bula/Model/DataSet.php");

/**
 * Logic for remote method invocation.
 */
class CallMethod extends Page {

    /** Execute method using parameters from request. */
    public function execute() {
        Request::initialize();
		Request::extractAllVars();

        // Check security code
		if (!Request::contains("code"))
			Response::end("Code is required!");
		$code = Request::get("code");
		if (!EQ($code, Config::SECURITY_CODE))
			Response::end("Incorrect code!");

		// Check package
        if (!Request::contains("package"))
			Response::end("Package is required!");
		$package = Request::get("package");
		if (BLANK($package))
			Response::end("Empty package!");
        $package_chunks = Strings::split("-", $package);
        for ($n = 0; $n < SIZE($package_chunks); $n++)
            $package_chunks[$n] = Strings::firstCharToUpper($package_chunks[$n]);
		$package = Strings::join("/", $package_chunks);

		// Check class
        if (!Request::contains("class"))
			Response::end("Class is required!");
		$className = Request::get("class");
		if (BLANK($className))
			Response::end("Empty class!");

		// Check method
        if (!Request::contains("method"))
			Response::end("Method is required!");
		$method = Request::get("method");
		if (BLANK($method))
			Response::end("Empty method!");

		// Fill array with parameters
        $count = 0;
		$pars = new ArrayList();
		for ($n = 1; $n <= 6; $n++) {
			$par_name = CAT("par", $n);
			if (!Request::contains($par_name))
				break;
			$par_value = Request::get($par_name);
			if (EQ($par_value, "_"))
				$par_value = "";
			//$pars_array[] = $par_value;
            $pars->add($par_value);
			$count++;
		}

        $buffer = null;
        $result = null;

        $full_class = CAT($package, "/", $className);
//if php
		$class_file = CAT($full_class, ".php");
		require_once($class_file);
		$full_class = Strings::replace("/", "\\", $full_class);
		$doClass = new $full_class;
		if ($doClass == null)
            Response::end("Can not instantiate class!");
        $reflectionMethod = new \ReflectionMethod($full_class, $method);
        $parameters = $reflectionMethod->getParameters();
        $count_required = 0;
        for ($n = 0; $n < SIZE($parameters); $n++) {
            $p = new \ReflectionParameter(array($full_class, $method), $n);
            if (!$p->isOptional()) $count_required++;
        }
        if ($pars->count() < $count_required)
            $result = null;
        else
            $result = $reflectionMethod->invokeArgs($doClass, $pars->toArray());
        //$buffer = str_replace(CAT(Config::$LocalRoot), "_ROOT_", ob_get_contents());

        if ($result == null)
            $buffer = "NULL";
        else if ($result instanceof DataSet)
            $buffer = $result->toXml();
        else
            $buffer = STR($result);
        Response::write($buffer);
	}
}
