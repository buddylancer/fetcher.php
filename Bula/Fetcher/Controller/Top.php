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

use Bula\Objects\DateTimes;
use Bula\Objects\THashtable;
use Bula\Objects\TString;

require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/THashtable.php");

/**
 * Logic for generating Top block.
 */
class Top extends Page
{

    /** Execute main logic for Top block */
    public function execute()
    {
        $prepare = new THashtable();
        $prepare->put("[#ImgWidth]", $this->context->IsMobile ? 234 : 468);
        $prepare->put("[#ImgHeight]", $this->context->IsMobile ? 30 : 60);
        if ($this->context->TestRun)
            $prepare->put("[#Date]", "28-Jun-2020 16:49 GMT");
        else
            $prepare->put("[#Date]", Util::showTime());

        $this->write("top", $prepare);
    }
}
