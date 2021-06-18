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
use Bula\Objects\Request;
use Bula\Objects\DataList;
use Bula\Objects\DataRange;
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
        if ($this->context->Request->contains("source"))
            $source = $this->context->Request->get("source");

        $prepare = new DataRange();
        if ($this->context->FineUrls)
            $prepare->put("[#Fine_Urls]", 1);
        $prepare->put("[#Selected]", BLANK($source) ? " selected=\"selected\" " : "");
        $dsSources = null;
        //TODO -- This can be too long on big databases... Switch off counters for now.
        $useCounters = true;
        if ($useCounters)
            $dsSources = $doSource->enumSourcesWithCounters();
        else
            $dsSources = $doSource->enumSources();
        $options = new DataList();
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);
            $option = new DataRange();
            $option->put("[#Selected]", ($oSource->get("s_SourceName")->equals($source) ? "selected=\"selected\"" : " "));
            $option->put("[#Id]", $oSource->get("s_SourceName"));
            $option->put("[#Name]", $oSource->get("s_SourceName"));
            if ($useCounters)
                $option->put("[#Counter]", $oSource->get("cntpro"));
            $options->add($option);
        }
        $prepare->put("[#Options]", $options);
        $this->write("Pages/filter_items", $prepare);
    }
}
