<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;

use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOCategory;

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Fetcher/Model/DOCategory.php");

/**
 * Logic for generating Bottom block.
 */
class Bottom extends Page
{

    /** Execute main logic for Bottom block */
    public function execute()
    {
        $prepare = new THashtable();
        $prepare->put("[#Items_By_Category]",
            CAT(Config::NAME_ITEMS, "_by_", $this->context->get("Name_Category")));

        $doCategory = new DOCategory($this->context->Connection);
        $dsCategory = $doCategory->enumAll(Config::SHOW_EMPTY ? null : "_this.i_Counter <> 0",
            Config::SORT_CATEGORIES == null ? null : CAT("_this.", Config::SORT_CATEGORIES));
        $size = $dsCategory->getSize();
        $size3 = $size % 3;
        $n1 = INT($size / 3) + ($size3 == 0 ? 0 : 1);
        $n2 = $n1 * 2;
        $nn = array(0, $n1, $n2, $size);
        $filterBlocks = new TArrayList();
        for ($td = 0; $td < 3; $td++) {
            $filterBlock = new THashtable();
            $rows = new TArrayList();
            for ($n = INT($nn[$td]); $n < INT($nn[$td+1]); $n++) {
                $oCategory = $dsCategory->getRow($n);
                if (NUL($oCategory))
                    continue;
                $counter = INT($oCategory->get("i_Counter"));
                if (Config::SHOW_EMPTY == false && INT($counter) == 0)
                    continue;
                $key = $oCategory->get("s_CatId");
                $name = $oCategory->get("s_Name");
                $row = new THashtable();
                $row->put("[#Link]", $this->getLink(Config::INDEX_PAGE, "?p=items&filter=", "items/filter/", $key));
                $row->put("[#LinkText]", $name);
                //if ($counter > 0)
                    $row->put("[#Counter]", $counter);
                $rows->add($row);
            }
            $filterBlock->put("[#Rows]", $rows);
            $filterBlocks->add($filterBlock);
        }
        $prepare->put("[#FilterBlocks]", $filterBlocks);

        if (!$this->context->IsMobile) {
            //$dsCategory = $doCategory->enumAll(null, Config::SORT_CATEGORIES == null ? null : CAT("_this.", Config::SORT_CATEGORIES));
            $size = $dsCategory->getSize(); //50
            $size3 = $size % 3; //2
            $n1 = INT($size / 3) + ($size3 == 0 ? 0 : 1); //17.3
            $n2 = $n1 * 2; //34.6
            $nn = array(0, $n1, $n2, $size);
            $rssBlocks = new TArrayList();
            for ($td = 0; $td < 3; $td++) {
                $rssBlock = new THashtable();
                $rows = new TArrayList();
                for ($n = INT($nn[$td]); $n < INT($nn[$td+1]); $n++) {
                    $oCategory = $dsCategory->getRow($n);
                    if (NUL($oCategory))
                        continue;
                    $key = $oCategory->get("s_CatId");
                    $name = $oCategory->get("s_Name");
                    //$counter = INT($oCategory->get("i_Counter"));
                    $row = new THashtable();
                    $row->put("[#Link]", $this->getLink(Config::RSS_PAGE, "?filter=", "rss/", CAT($key, ($this->context->FineUrls ? ".xml" : null))));
                    $row->put("[#LinkText]", $name);
                    $rows->add($row);
                }
                $rssBlock->put("[#Rows]", $rows);
                $rssBlocks->add($rssBlock);
            }
            $prepare->put("[#RssBlocks]", $rssBlocks);
        }
        $this->write("bottom", $prepare);
    }
}
