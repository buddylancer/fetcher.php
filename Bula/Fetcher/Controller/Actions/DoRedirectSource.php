<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Actions;

use Bula\Fetcher\Config;

use Bula\Objects\Request;
use Bula\Objects\TString;
use Bula\Objects\Hashtable;

use Bula\Fetcher\Model\DOSource;

require_once("Bula/Fetcher/Model/DOSource.php");
require_once("Bula/Fetcher/Controller/Actions/DoRedirect.php");

/**
 * Redirection to external source.
 */
class DoRedirectSource extends DoRedirect {

    /** Execute main logic for this action */
    public function execute() {
        $error_message = null;
        $link_to_redirect = null;
        if (!Request::contains("source"))
            $error_message = "Source name is required!";
        else {
            $source_name = Request::get("source");
            if (!Request::isDomainName($source_name))
                $error_message = "Incorrect source name!";
            else {
                $doSource = new DOSource();
                $oSource =
                    ARR(new Hashtable());
                if (!$doSource->checkSourceName($source_name, $oSource))
                    $error_message = "No such source name!";
                else
                    $link_to_redirect = $oSource[0]->get("s_External");
            }
        }
        $this->executeRedirect($link_to_redirect, $error_message);
    }
}
