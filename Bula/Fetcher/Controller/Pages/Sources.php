<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
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
class Sources extends ItemsBase {

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check() {
        return new Hashtable();
    }

    /**
     * Execute main logic for Source block.
     */
    public function execute() {
        $Prepare = new Hashtable();

        $doSource = new DOSource();
        $doItem = new DOItem();

        $dsSources = $doSource->enumSources();
        $count = 1;
        $Sources = new ArrayList();
        for ($ns = 0; $ns < $dsSources->getSize(); $ns++) {
            $oSource = $dsSources->getRow($ns);
            $source_name = $oSource->get("s_SourceName");

            $SourceRow = new Hashtable();
            $SourceRow->put("[#SourceName]", $source_name);
            //$SourceRow["[#RedirectSource]"] = Config::TOP_DIR .
            //    (Config::FINE_URLS ? "redirect/source/" : "action.php?p=do_redirect_source&source=") .
            //        $oSource["s_SourceName"];
            $SourceRow->put("[#RedirectSource]", CAT(Config::TOP_DIR,
                ($this->context->FineUrls ? "items/source/" : CAT(Config::INDEX_PAGE, "?p=items&source=")), $source_name));

            $dsItems = $doItem->enumItemsFromSource(null, $source_name, null, 3);
            $Items = new ArrayList();
            $item_count = 0;
            for ($ni = 0; $ni < $dsItems->getSize(); $ni++) {
                $oItem = $dsItems->getRow($ni);
                $Items->add(parent::fillItemRow($oItem, $doItem->getIdField(), $item_count));
                $item_count++;
            }
            $SourceRow->put("[#Items]", $Items);

            $Sources->add($SourceRow);
            $count++;
        }
        $Prepare->put("[#Sources]", $Sources);

        $this->write("Bula/Fetcher/View/Pages/sources.html", $Prepare);
    }
}
