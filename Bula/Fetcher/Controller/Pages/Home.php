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
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Model/DOItem.php");
require_once("ItemsBase.php");

/**
 * Controller for Home block.
 */
class Home extends ItemsBase {

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check() {
        return new Hashtable();
    }

    /**
     * Execute main logic for Home block.
     */
    public function execute() {
        $Pars = $this->check();
        if ($Pars == null)
            return;

        $Prepare = new Hashtable();

        $doItem = new DOItem();

        $all_items_href =
            CAT(Config::TOP_DIR, ($this->context->FineUrls ? null : CAT(Config::INDEX_PAGE, "?p=")), "items");
        $Prepare->put("[#BrowseItemsLink]", $all_items_href);

        $source = null;
        $search = null;
        $max_rows = Config::DB_HOME_ROWS;
        $dsItems = $doItem->enumItems($source, $search, 1, $max_rows);
        $row_count = 1;
        $Items = new ArrayList();
        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $Row = parent::fillItemRow($oItem, $doItem->getIdField(), $row_count);
            $Items->add($Row);
            $row_count++;
        }
        $Prepare->put("[#Items]", $Items);

        $this->write("Bula/Fetcher/View/Pages/home.html", $Prepare);
    }
}
