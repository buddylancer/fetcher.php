<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Testing;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
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
class CallMethod extends Page
{

    /** Execute method using parameters from request. */
    public function execute()
    {
        //$this->context->Request->initialize();
        $this->context->Request->extractAllVars();

        $this->context->Response->writeHeader("Content-type", "text/html; charset=UTF-8");

        // Check security code
        if (!$this->context->Request->contains("code")) {
            $this->context->Response->end("Code is required!");
            return;
        }
        $code = $this->context->Request->get("code");
        if (!EQ($code, Config::SECURITY_CODE)) {
            $this->context->Response->end("Incorrect code!");
            return;
        }

        // Check package
        if (!$this->context->Request->contains("package")) {
            $this->context->Response->end("Package is required!");
            return;
        }
        $package = $this->context->Request->get("package");
        if (BLANK($package)) {
            $this->context->Response->end("Empty package!");
            return;
        }
        $packageChunks = Strings::split("-", $package);
        for ($n = 0; $n < SIZE($packageChunks); $n++)
            $packageChunks[$n] = Strings::firstCharToUpper($packageChunks[$n]);
        $package = Strings::join("/", $packageChunks);

        // Check class
        if (!$this->context->Request->contains("class")) {
            $this->context->Response->end("Class is required!");
            return;
        }
        $className = $this->context->Request->get("class");
        if (BLANK($className)) {
            $this->context->Response->end("Empty class!");
            return;
        }

        // Check method
        if (!$this->context->Request->contains("method")) {
            $this->context->Response->end("Method is required!");
            return;
        }
        $method = $this->context->Request->get("method");
        if (BLANK($method)) {
            $this->context->Response->end("Empty method!");
            return;
        }

        // Fill array with parameters
        $count = 0;
        $pars = new ArrayList();
        for ($n = 1; $n <= 6; $n++) {
            $parName = CAT("par", $n);
            if (!$this->context->Request->contains($parName))
                break;
            $parValue = $this->context->Request->get($parName);
            if (EQ($parValue, "_"))
                $parValue = "";
            //$parsArray[] = $parValue;
            $pars->add($parValue);
            $count++;
        }

        $buffer = null;
        $result = null;

        $fullClass = CAT($package, "/", $className);
        $classFile = CAT($fullClass, ".php");
        require_once($classFile);
        $fullClass = Strings::replace("/", "\\", $fullClass);
        $doClass = new $fullClass;
        if ($doClass == null) {
            $this->context->Response->end("Can not instantiate class!");
            return;
        }
        $reflectionMethod = new \ReflectionMethod($fullClass, $method);
        $parameters = $reflectionMethod->getParameters();
        $countRequired = 0;
        for ($n = 0; $n < SIZE($parameters); $n++) {
            $p = new \ReflectionParameter(array($fullClass, $method), $n);
            if (!$p->isOptional()) $countRequired++;
        }
        if ($pars->size() < $countRequired)
            $result = null;
        else
            $result = $reflectionMethod->invokeArgs($doClass, $pars->toArray());
        //$buffer = str_replace(CAT(Config::$LocalRoot), "_ROOT_", ob_get_contents());

        if ($result == null)
            $buffer = "NULL";
        else if ($result instanceof DataSet)
            $buffer = $result->toXml(EOL);
        else
            $buffer = STR($result);
        $this->context->Response->write($buffer);
        $this->context->Response->end();
    }
}
