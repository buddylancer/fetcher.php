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

use Bula\Objects\TRequest;
use Bula\Objects\TString;

use Bula\Model\DataSet;

use Bula\Fetcher\Controller\Engine;

use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Model\DOSource;
use Bula\Fetcher\Model\DOCategory;

require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Model/DOCategory.php");
require_once("Bula/Fetcher/Model/DOSource.php");
require_once("ItemsBase.php");

/**
 * Controller for Items block.
 */
class Items extends ItemsBase
{

    /**
     * Fast check of input query parameters.
     * @return THashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        $errorMessage = new TString();

        $list = $this->context->Request->get("list");
        if (!NUL($list)) {
            if (BLANK($list))
                $errorMessage->concat("Empty list number!");
            else if (!TRequest::isInteger($list))
                $errorMessage->concat("Incorrect list number!");
        }

        $sourceName = $this->context->Request->get("source");
        if (!NUL($sourceName)) {
            if (BLANK($sourceName)) {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat("<br/>");
                $errorMessage->concat("Empty source name!");
            }
            else if (!TRequest::isDomainName($sourceName)) {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat("<br/>");
                $errorMessage->concat("Incorrect source name!");
            }
        }

        $filterName = $this->context->Request->get("filter");
        if (!NUL($filterName)) {
            if (BLANK($filterName)) {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat("<br/>");
                $errorMessage->concat("Empty filter name!");
            }
            else if (!TRequest::isName($filterName)) {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat("<br/>");
                $errorMessage->concat("Incorrect filter name!");
            }
        }

        if ($errorMessage->length() > 0) {
            $prepare = new THashtable();
            $prepare->put("[#ErrMessage]", $errorMessage);
            $this->write("error", $prepare);
            return null;
        }

        $pars = new THashtable();
        if (!NUL($list))
            $pars->put("list", $list);
        if (!NUL($sourceName))
            $pars->put("source_name", $sourceName);
        if (!NUL($filterName))
            $pars->put("filter_name", $filterName);
        return $pars;
    }

    /** Execute main logic for Items block. */
    public function execute()
    {
        $pars = $this->check();
        if ($pars == null)
            return;

        $list = $pars->get("list");
        $listNumber = $list == null ? 1 : INT($list);
        $sourceName = $pars->get("source_name");
        $filterName = $pars->get("filter_name");

        $errorMessage = new TString();
        $filter = null;
        $category = null;

        if (!NUL($filterName)) {
            $doCategory = new DOCategory($this->context->Connection);
            $oCategory =
                ARR(new THashtable());
            if (!$doCategory->checkFilterName($filterName, $oCategory))
                $errorMessage->concat("Non-existing filter name!");
            else  {
                $category = $oCategory[0]->get("s_Name");
                $filter = $oCategory[0]->get("s_Filter");
            }
        }

        $sourceId = -1;
        if (!NUL($sourceName)) {
            $doSource = new DOSource($this->context->Connection);
            $oSource =
                ARR(new THashtable());
            if (!$doSource->checkSourceName($sourceName, $oSource)) {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat("<br/>");
                $errorMessage->concat("Non-existing source name!");
            }
            else
                $sourceId = INT($oSource[0]->get("i_SourceId"));
        }

        $engine = $this->context->getEngine();

        $prepare = new THashtable();
        if ($errorMessage->length() > 0) {
            $prepare->put("[#ErrMessage]", $errorMessage);
            $this->write("error", $prepare);
            return;
        }

        if (Config::SHOW_IMAGES)
            $prepare->put("[#Show_Images]", 1);
        $prepare->put("[#ColSpan]", Config::SHOW_IMAGES ? 4 : 3);

        // Uncomment to enable filtering by source and/or category
        $prepare->put("[#FilterItems]", $engine->includeTemplate("Pages/FilterItems"));

        $s_Title = CAT(
            "Browse ",
            Config::NAME_ITEMS,
            ($this->context->IsMobile ? "<br/>" : null),
            (!BLANK($sourceName) ? CAT(" ... from '", $sourceName, "'") : null),
            (!BLANK($filter) ? CAT(" ... for '", $category, "'") : null)
        );

        $prepare->put("[#Title]", $s_Title);

        $maxRows = Config::DB_ITEMS_ROWS;

        $doItem = new DOItem($this->context->Connection);
        //$realFilter = DOItem::buildSqlByFilter($filter);
        $realFilter = DOItem::buildSqlByCategory($category);
        $dsItems = $doItem->enumItems($sourceName, $realFilter, $listNumber, $maxRows);

        $listTotal = $dsItems->getTotalPages();
        if ($listNumber > $listTotal) {
            if ($listTotal > 0) {
                $prepare->put("[#ErrMessage]", "List number is too large!");
                $this->write("error", $prepare);
                return;
            }
            else {
                $prepare->put("[#ErrMessage]", "Empty list!");
                $this->write("error", $prepare);
                return;
            }
        }
        if ($listTotal > 1) {
            $prepare->put("[#List_Total]", $listTotal);
            $prepare->put("[#List]", $listNumber);
        }

        $count = 1;
        $rows = new TArrayList();
        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $row = parent::fillItemRow($oItem, $doItem->getIdField(), $count);
            $count++;
            $rows->add($row);
        }
        $prepare->put("[#Rows]", $rows);

        if ($listTotal > 1) {
            $chunk = 2;
            $before = false;
            $after = false;

            $pages = new TArrayList();
            for ($n = 1; $n <= $listTotal; $n++) {
                $page = new THashtable();
                if ($n < $listNumber - $chunk) {
                    if (!$before) {
                        $before = true;
                        $page->put("[#Text]", "1");
                        $page->put("[#Link]", parent::getPageLink(1));
                        $pages->add($page);
                        $page = new THashtable();
                        $page->put("[#Text]", " ... ");
                        //$row->remove("[#Link]");
                        $pages->add($page);
                    }
                    continue;
                }
                if ($n > $listNumber + $chunk) {
                    if (!$after) {
                        $after = true;
                        $page->put("[#Text]", " ... ");
                        $pages->add($page);
                        $page = new THashtable();
                        $page->put("[#Text]", $listTotal);
                        $page->put("[#Link]", parent::getPageLink($listTotal));
                        $pages->add($page);
                    }
                    continue;
                }
                if ($listNumber == $n) {
                    $page->put("[#Text]", CAT("=", $n, "="));
                    $pages->add($page);
                }
                else {
                    if ($n == 1) {
                        $page->put("[#Link]", parent::getPageLink(1));
                        $page->put("[#Text]", 1);
                    }
                    else  {
                        $page->put("[#Link]", parent::getPageLink($n));
                        $page->put("[#Text]", $n);
                    }
                    $pages->add($page);
                }
            }
            $prepare->put("[#Pages]", $pages);
        }

        $this->write("Pages/items", $prepare);
    }
}
