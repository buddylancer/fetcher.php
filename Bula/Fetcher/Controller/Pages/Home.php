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
     * @return THashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        return new THashtable();
    }

    /** Execute main logic for Home block. */
    public function execute()
    {
        $pars = $this->check();
        if ($pars == null)
            return;

        $prepare = new THashtable();

        $doItem = new DOItem();

        $prepare->put("[#BrowseItemsLink]", $this->getLink(Config::INDEX_PAGE, "?p=", null, "items"));
        if (Config::SHOW_IMAGES)
            $prepare->put("[#Show_Images]", 1);

        $source = null;
        $search = null;
        $maxRows = Config::DB_HOME_ROWS;
        $dsItems = $doItem->enumItems($source, $search, 1, $maxRows);
        $rowCount = 1;
        $items = new TArrayList();
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
