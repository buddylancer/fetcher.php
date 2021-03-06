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
use Bula\Fetcher\Context;
use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
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
     * @return THashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        return new THashtable();
    }

    /** Execute main logic for Source block. */
    public function execute()
    {
        $prepare = new THashtable();
        if (Config::SHOW_IMAGES)
            $prepare->put("[#Show_Images]", 1);

        $doSource = new DOSource($this->context->Connection);
        $doItem = new DOItem($this->context->Connection);

        $dsSources = $doSource->enumSources();
        $count = 1;
        $sources = new TArrayList();
        for ($ns = 0; $ns < $dsSources->getSize(); $ns++) {
            $oSource = $dsSources->getRow($ns);
            $sourceName = $oSource->get("s_SourceName");

            $sourceRow = new THashtable();
            $sourceRow->put("[#ColSpan]", Config::SHOW_IMAGES ? 4 : 3);
            $sourceRow->put("[#SourceName]", $sourceName);
            $sourceRow->put("[#ExtImages]", Config::EXT_IMAGES);
            //$sourceRow["[#RedirectSource]"] = Config::TOP_DIR .
            //    (Config::FINE_URLS ? "redirect/source/" : "action.php?p=do_redirect_source&source=") .
            //        $oSource["s_SourceName"];
            $sourceRow->put("[#RedirectSource]", $this->getLink(Config::INDEX_PAGE, "?p=items&source=", "items/source/", $sourceName));

            $dsItems = $doItem->enumItemsFromSource(null, $sourceName, null, 3);
            $items = new TArrayList();
            $itemCount = 0;
            for ($ni = 0; $ni < $dsItems->getSize(); $ni++) {
                $oItem = $dsItems->getRow($ni);
                $item = parent::fillItemRow($oItem, $doItem->getIdField(), $itemCount);
                if (Config::SHOW_IMAGES)
                    $item->put("[#Show_Images]", 1);
                $items->add($item);
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
