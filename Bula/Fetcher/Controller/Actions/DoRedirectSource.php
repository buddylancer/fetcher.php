<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Actions;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;

use Bula\Objects\TRequest;
use Bula\Objects\TString;
use Bula\Objects\THashtable;

use Bula\Fetcher\Model\DOSource;

require_once("Bula/Fetcher/Model/DOSource.php");
require_once("Bula/Fetcher/Controller/Actions/DoRedirect.php");

/**
 * Redirection to external source.
 */
class DoRedirectSource extends DoRedirect
{

    /** Execute main logic for DoRedirectSource action */
    public function execute()
    {
        $errorMessage = null;
        $linkToRedirect = null;
        if (!$this->context->Request->contains("source"))
            $errorMessage = "Source name is required!";
        else {
            $sourceName = $this->context->Request->get("source");
            if (!TRequest::isDomainName($sourceName))
                $errorMessage = "Incorrect source name!";
            else {
                $doSource = new DOSource($this->context->Connection);
                $oSource =
                    ARR(new THashtable());
                if (!$doSource->checkSourceName($sourceName, $oSource))
                    $errorMessage = "No such source name!";
                else
                    $linkToRedirect = $oSource[0]->get("s_External");
            }
        }
        $this->executeRedirect($linkToRedirect, $errorMessage);
    }
}
