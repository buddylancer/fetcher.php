<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Fetcher\Config;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOCategory;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Fetcher/Model/DOCategory.php");

/**
 * Logic for generating Bottom block.
 */
class Bottom extends Page {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public Bottom(Context context) : base(context) { }
    CS*/

    /** Execute main logic for Bottom block */
    public function execute() {
        $Prepare = new Hashtable();

        $filter_link = CAT(Config::TOP_DIR,
            ($this->context->FineUrls ? "items/filter/" : CAT(Config::INDEX_PAGE, "?p=items&filter=")));

        $doCategory = new DOCategory();
        $dsCategory = $doCategory->enumAll("_this.i_Counter <> 0");
        $size = $dsCategory->getSize();
        $size3 = $size % 3;
        $n1 = INT($size / 3) + ($size3 == 0 ? 0 : 1);
        $n2 = $n1 * 2;
        $nn = array(0, $n1, $n2, $size);
        $FilterBlocks = new ArrayList();
        for ($td = 0; $td < 3; $td++) {
            $FilterBlock = new Hashtable();
            $Rows = new ArrayList();
            for ($n = INT($nn[$td]); $n < INT($nn[$td+1]); $n++) {
                $oCategory = $dsCategory->getRow($n);
                $counter = INT($oCategory->get("i_Counter"));
                if (INT($counter) == 0)
                    continue;
                $key = $oCategory->get("s_CatId");
                $name = $oCategory->get("s_Name");
                $Row = new Hashtable();
                $href = CAT($filter_link, $key);
                $Row->put("[#Link]", $href);
                $Row->put("[#LinkText]", $name);
                //if ($counter > 0)
                    $Row->put("[#Counter]", $counter);
                $Rows->add($Row);
            }
            $FilterBlock->put("[#Rows]", $Rows);
            $FilterBlocks->add($FilterBlock);
        }
        $Prepare->put("[#FilterBlocks]", $FilterBlocks);

        if (!$this->context->IsMobile) {
            $filter_link = CAT(Config::TOP_DIR,
                ($this->context->FineUrls ? "rss/" : CAT(Config::RSS_PAGE, "?filter=")));
            $dsCategory = $doCategory->enumAll();
            $size = $dsCategory->getSize(); //50
            $size3 = $size % 3; //2
            $n1 = INT($size / 3) + ($size3 == 0 ? 0 : 1); //17.3
            $n2 = $n1 * 2; //34.6
            $nn = array(0, $n1, $n2, $size);
            $RssBlocks = new ArrayList();
            for ($td = 0; $td < 3; $td++) {
                $RssBlock = new Hashtable();
                $Rows = new ArrayList();
                for ($n = INT($nn[$td]); $n < INT($nn[$td+1]); $n++) {
                    $oCategory = $dsCategory->getRow($n);
                    $key = $oCategory->get("s_CatId");
                    $name = $oCategory->get("s_Name");
                    //$counter = INT($oCategory->get("i_Counter"));
                    $Row = new Hashtable();
                    $href = CAT($filter_link, $key, ($this->context->FineUrls ? ".xml" : null));
                    $Row->put("[#Link]", $href);
                    $Row->put("[#LinkText]", $name);
                    $Rows->add($Row);
                }
                $RssBlock->put("[#Rows]", $Rows);
                $RssBlocks->add($RssBlock);
            }
            $Prepare->put("[#RssBlocks]", $RssBlocks);
        }
        $this->write("Bula/Fetcher/View/bottom.html", $Prepare);
    }
}
