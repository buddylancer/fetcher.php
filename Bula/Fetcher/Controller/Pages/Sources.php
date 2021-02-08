<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Pages;

use Bula\Fetcher\Config;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOSource;
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Model/DOSource.php");
require_once("Bula/Fetcher/Model/DOItem.php");
require_once("ItemsBase.php");

/**
 * Controller for Sources block.
 */
class Sources extends ItemsBase
{

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        return new Hashtable();
    }

    /** Execute main logic for Source block. */
    public function execute()
    {
        $prepare = new Hashtable();

        $doSource = new DOSource();
        $doItem = new DOItem();

        $dsSources = $doSource->enumSources();
        $count = 1;
        $sources = new ArrayList();
        for ($ns = 0; $ns < $dsSources->getSize(); $ns++) {
            $oSource = $dsSources->getRow($ns);
            $sourceName = $oSource->get("s_SourceName");

            $sourceRow = new Hashtable();
            $sourceRow->put("[#SourceName]", $sourceName);
            //$sourceRow["[#RedirectSource]"] = Config::TOP_DIR .
            //    (Config::FINE_URLS ? "redirect/source/" : "action.php?p=do_redirect_source&source=") .
            //        $oSource["s_SourceName"];
            $sourceRow->put("[#RedirectSource]", $this->getLink(Config::INDEX_PAGE, "?p=items&source=", "items/source/", $sourceName));

            $dsItems = $doItem->enumItemsFromSource(null, $sourceName, null, 3);
            $items = new ArrayList();
            $itemCount = 0;
            for ($ni = 0; $ni < $dsItems->getSize(); $ni++) {
                $oItem = $dsItems->getRow($ni);
                $items->add(parent::fillItemRow($oItem, $doItem->getIdField(), $itemCount));
                $itemCount++;
            }
            $sourceRow->put("[#Items]", $items);

            $sources->add($sourceRow);
            $count++;
        }
        $prepare->put("[#Sources]", $sources);

        $this->write("Pages/sources", $prepare);
    }
}
