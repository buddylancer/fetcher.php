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

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

use Bula\Objects\Request;
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
class Items extends ItemsBase {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public Items(Context context) : base(context) { }
    CS*/

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check() {
        $error_message = new TString();

        $list = Request::get("list");
        if (!NUL($list)) {
            if (BLANK($list))
                $error_message->concat("Empty list number!");
            else if (!Request::isInteger($list))
                $error_message->concat("Incorrect list number!");
        }

        $source_name = Request::get("source");
        if (!NUL($source_name)) {
            if (BLANK($source_name)) {
                if ($error_message->length() > 0)
                    $error_message->concat("<br/>");
                $error_message->concat("Empty source name!");
            }
            else if (!Request::isDomainName($source_name)) {
                if ($error_message->length() > 0)
                    $error_message->concat("<br/>");
                $error_message->concat("Incorrect source name!");
            }
        }

        $filter_name = Request::get("filter");
        if (!NUL($filter_name)) {
            if (BLANK($filter_name)) {
                if ($error_message->length() > 0)
                    $error_message->concat("<br/>");
                $error_message->concat("Empty filter name!");
            }
            else if (!Request::isName($filter_name)) {
                if ($error_message->length() > 0)
                    $error_message->concat("<br/>");
                $error_message->concat("Incorrect filter name!");
            }
        }

        if ($error_message->length() > 0) {
            $Prepare = new Hashtable();
            $Prepare->put("[#ErrMessage]", $error_message);
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return null;
        }

        $Pars = new Hashtable();
        if (!NUL($list))
            $Pars->put("list", $list);
        if (!NUL($source_name))
            $Pars->put("source_name", $source_name);
        if (!NUL($filter_name))
            $Pars->put("filter_name", $filter_name);
        return $Pars;
    }

    /** Execute main logic for Items block. */
    public function execute() {
        $Pars = $this->check();
        if ($Pars == null)
            return;

        $list = /*(TString)*/$Pars->get("list");
        $list_number = $list == null ? 1 : INT($list);
        $source_name = /*(TString)*/$Pars->get("source_name");
        $filter_name = /*(TString)*/$Pars->get("filter_name");

        $error_message = new TString();
        $filter = null;

        if (!NUL($filter_name)) {
            $doCategory = new DOCategory();
            $oCategory =
                ARR(new Hashtable());
            if (!$doCategory->checkFilterName($filter_name, $oCategory))
                $error_message->concat("Non-existing filter name!");
            else
                $filter = $oCategory[0]->get("s_Filter");
        }

        if (!NUL($source_name)) {
            $doSource = new DOSource();
            $oSource =
                ARR(new Hashtable());
            if (!$doSource->checkSourceName($source_name, $oSource)) {
                if ($error_message->length() > 0)
                    $error_message->concat("<br/>");
                $error_message->concat("Non-existing source name!");
            }
        }

        $engine = $this->context->getEngine();

        $Prepare = new Hashtable();
        if ($error_message->length() > 0) {
            $Prepare->put("[#ErrMessage]", $error_message);
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return;
        }

        // Uncomment to enable filtering by source and/or category
        $Prepare->put("[#FilterItems]", $engine->includeTemplate("Bula/Fetcher/Controller/Pages/FilterItems"));

        $s_Title = CAT(
            "Browse ",
            Config::NAME_ITEMS,
            ($this->context->IsMobile ? "<br/>" : null),
            (!BLANK($source_name) ? CAT(" ... from '", $source_name, "'") : null),
            (!BLANK($filter) ? CAT(" ... for '", $filter_name, "'") : null)
        );

        $Prepare->put("[#Title]", $s_Title);

        $max_rows = Config::DB_ITEMS_ROWS;

        $doItem = new DOItem();
        $dsItems = $doItem->enumItems($source_name, $filter, $list_number, $max_rows);

        $list_total = $dsItems->getTotalPages();
        if ($list_number > $list_total) {
            $Prepare->put("[#ErrMessage]", "List number is too large!");
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return;
        }
        if ($list_total > 1) {
            $Prepare->put("[#List_Total]", $list_total);
            $Prepare->put("[#List]", $list_number);
        }

        $count = 1;
        $Rows = new ArrayList();
        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $Row = parent::fillItemRow($oItem, $doItem->getIdField(), $count);
            $count++;
            $Rows->add($Row);
        }
        $Prepare->put("[#Rows]", $Rows);

        if ($list_total > 1) {
            $chunk = 2;
            $before = false;
            $after = false;

            $Pages = new ArrayList();
            for ($n = 1; $n <= $list_total; $n++) {
                $Page = new Hashtable();
                if ($n < $list_number - $chunk) {
                    if (!$before) {
                        $before = true;
                        $Page->put("[#Text]", "1");
                        $Page->put("[#Link]", parent::getPageLink(1));
                        $Pages->add($Page);
                        $Page = new Hashtable();
                        $Page->put("[#Text]", " ... ");
                        //$Row->remove("[#Link]");
                        $Pages->add($Page);
                    }
                    continue;
                }
                if ($n > $list_number + $chunk) {
                    if (!$after) {
                        $after = true;
                        $Page->put("[#Text]", " ... ");
                        $Pages->add($Page);
                        $Page = new Hashtable();
                        $Page->put("[#Text]", $list_total);
                        $Page->put("[#Link]", parent::getPageLink($list_total));
                        $Pages->add($Page);
                    }
                    continue;
                }
                if ($list_number == $n) {
                    $Page->put("[#Text]", CAT("=", $n, "="));
                    $Pages->add($Page);
                }
                else {
                    if ($n == 1) {
                        $Page->put("[#Link]", parent::getPageLink(1));
                        $Page->put("[#Text]", 1);
                    }
                    else  {
                        $Page->put("[#Link]", parent::getPageLink($n));
                        $Page->put("[#Text]", $n);
                    }
                    $Pages->add($Page);
                }
            }
            $Prepare->put("[#Pages]", $Pages);
        }

        $this->write("Bula/Fetcher/View/Pages/items.html", $Prepare);
    }
}
