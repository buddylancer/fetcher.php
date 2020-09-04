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

use Bula\Objects\Hashtable;

use Bula\Fetcher\Config;
use Bula\Objects\TString;
use Bula\Objects\DateTimes;

require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Hashtable.php");

/**
 * Logic for generating Top block.
 */
class Top extends Page {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public Top(Context context) : base(context) { }
    CS*/

    /** Execute main logic for Top block */
    public function execute() {
        $Prepare = new Hashtable();
        $Prepare->put("[#ImgWidth]", $this->context->IsMobile ? 234 : 468);
        $Prepare->put("[#ImgHeight]", $this->context->IsMobile ? 30 : 60);
        if ($this->context->TestRun)
            $Prepare->put("[#Date]", "28-Jun-2020 16:49 GMT");
        else
            $Prepare->put("[#Date]", Util::showTime(DateTimes::gmtFormat(Config::SQL_DTS)));

        $this->write("Bula/Fetcher/View/top.html", $Prepare);
    }
}
