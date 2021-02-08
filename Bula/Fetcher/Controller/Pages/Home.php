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
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Model/DOItem.php");
require_once("ItemsBase.php");

/**
 * Controller for Home block.
 */
class Home extends ItemsBase
{

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        return new Hashtable();
    }

    /** Execute main logic for Home block. */
    public function execute()
    {
        $pars = $this->check();
        if ($pars == null)
            return;

        $prepare = new Hashtable();

        $doItem = new DOItem();

        $prepare->put("[#BrowseItemsLink]", $this->getLink(Config::INDEX_PAGE, "?p=", null, "items"));

        $source = null;
        $search = null;
        $maxRows = Config::DB_HOME_ROWS;
        $dsItems = $doItem->enumItems($source, $search, 1, $maxRows);
        $rowCount = 1;
        $items = new ArrayList();
        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $row = parent::fillItemRow($oItem, $doItem->getIdField(), $rowCount);
            $items->add($row);
            $rowCount++;
        }
        $prepare->put("[#Items]", $items);

        $this->write("Pages/home", $prepare);
    }
}
