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

use Bula\Objects\Hashtable;
use Bula\Objects\Request;
use Bula\Objects\TString;

use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOItem;

require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Controller/Actions/DoRedirect.php");

/**
 * Redirecting to the external item.
 */
class DoRedirectItem extends DoRedirect {

    /** Execute main logic for this action */
    public function execute() {
        $error_message = null;
        $link_to_redirect = null;
        if (!Request::contains("id"))
            $error_message = "Item ID is required!";
        else {
            $id = Request::get("id");
            if (!Request::isInteger($id) || INT($id) <= 0)
                $error_message = "Incorrect item ID!";
            else {
                $doItem = new DOItem();
                $dsItems = $doItem->getById(INT($id));
                if ($dsItems->getSize() == 0)
                    $error_message = "No item with such ID!";
                else {
                    $oItem = $dsItems->getRow(0);
                    $link_to_redirect = $oItem->get("s_Link");
                }
            }
        }
        $this->executeRedirect($link_to_redirect, $error_message);
    }
}
