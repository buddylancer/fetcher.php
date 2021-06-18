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

use Bula\Objects\DataRange;
use Bula\Objects\Request;
use Bula\Objects\TString;

use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOItem;

require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Controller/Actions/DoRedirect.php");

/**
 * Redirecting to the external item.
 */
class DoRedirectItem extends DoRedirect
{

    /** Execute main logic for DoRedirectItem action */
    public function execute()
    {
        $errorMessage = null;
        $linkToRedirect = null;
        if (!$this->context->Request->contains("id"))
            $errorMessage = "Item ID is required!";
        else {
            $id = $this->context->Request->get("id");
            if (!Request::isInteger($id) || INT($id) <= 0)
                $errorMessage = "Incorrect item ID!";
            else {
                $doItem = new DOItem();
                $dsItems = $doItem->getById(INT($id));
                if ($dsItems->getSize() == 0)
                    $errorMessage = "No item with such ID!";
                else {
                    $oItem = $dsItems->getRow(0);
                    $linkToRedirect = $oItem->get("s_Link");
                }
            }
        }
        $this->executeRedirect($linkToRedirect, $errorMessage);
    }
}
