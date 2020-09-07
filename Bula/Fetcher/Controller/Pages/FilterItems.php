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
class FilterItems extends Page
{

    /** Execute main logic for FilterItems block. */
    public function execute()
    {
        $doSource = new DOSource();

        $source = null;
        if (Request::contains("source"))
            $source = Request::get("source");

        $prepare = new Hashtable();
        if ($this->context->FineUrls)
            $prepare->put("[#Fine_Urls]", 1);
        $prepare->put("[#Selected]", BLANK($source) ? " selected=\"selected\" " : null);
        $dsSources = null;
        //TODO -- This can be too long on big databases... Switch off counters for now.
        $useCounters = true;
        if ($useCounters)
            $dsSources = $doSource->enumSourcesWithCounters();
        else
            $dsSources = $doSource->enumSources();
        $options = new ArrayList();
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);
            $option = new Hashtable();
            $option->put("[#Selected]", ($oSource->get("s_SourceName")->equals($source) ? "selected=\"selected\"" : " "));
            $option->put("[#Id]", $oSource->get("s_SourceName"));
            $option->put("[#Name]", $oSource->get("s_SourceName"));
            if ($useCounters)
                $option->put("[#Counter]", $oSource->get("cntpro"));
            $options->add($option);
        }
        $prepare->put("[#Options]", $options);
        $this->write("Bula/Fetcher/View/Pages/filter_items.html", $prepare);
    }
}
