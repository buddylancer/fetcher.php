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
use Bula\Objects\Request;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOSource;
use Bula\Fetcher\Controller\Engine;
use Bula\Fetcher\Controller\Page;

require_once("Bula/Fetcher/Model/DOSource.php");

/**
 * Controller for Filter Items block.
 */
class FilterItems extends Page {

    /**
     * Execute main logic for Items block.
     */
    public function execute() {
        $doSource = new DOSource();

        $source = null;
        if (Request::contains("source"))
            $source = Request::get("source");

        $Prepare = new Hashtable();
        if ($this->context->FineUrls)
            $Prepare->put("[#Fine_Urls]", 1);
        $Prepare->put("[#Selected]", BLANK($source) ? " selected=\"selected\" " : null);
        $dsSources = null;
        //TODO -- This can be too long on big databases... Switch off counters for now.
        $useCounters = true;
        if ($useCounters)
            $dsSources = $doSource->enumSourcesWithCounters();
        else
            $dsSources = $doSource->enumSources();
        $Options = new ArrayList();
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);
            $Option = new Hashtable();
            $Option->put("[#Selected]", ($oSource->get("s_SourceName")->equals($source) ? "selected=\"selected\"" : " "));
            $Option->put("[#Id]", $oSource->get("s_SourceName"));
            $Option->put("[#Name]", $oSource->get("s_SourceName"));
            if ($useCounters)
                $Option->put("[#Counter]", $oSource->get("cntpro"));
            $Options->add($Option);
        }
        $Prepare->put("[#Options]", $Options);
        $this->write("Bula/Fetcher/View/Pages/filter_items.html", $Prepare);
    }
}
