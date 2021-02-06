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
use Bula\Objects\Request;
use Bula\Objects\Hashtable;
use Bula\Model\DataSet;
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Util;
use Bula\Fetcher\Controller\Page;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Model/DOItem.php");

/**
 * Controller for View Item block.
 */
class ViewItem extends Page
{

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check()
    {
        $prepare = new Hashtable();
        if (!Request::contains("id")) {
            $prepare->put("[#ErrMessage]", "Item ID is required!");
            $this->write("error", $prepare);
            return null;
        }
        $id = Request::get("id");
        if (!Request::isInteger($id)) {
            $prepare->put("[#ErrMessage]", "Item ID must be positive integer!");
            $this->write("error", $prepare);
            return null;
        }

        $pars = new Hashtable();
        $pars->put("id", $id);
        return $pars;
    }

    /** Execute main logic for View Item block. */
    public function execute()
    {
        $pars = self::check();
        if ($pars == null)
            return;

        $id = $pars->get("id");

        $prepare = new Hashtable();

        $doItem = new DOItem();
        $dsItems = $doItem->getById(INT($id));
        if ($dsItems == null || $dsItems->getSize() == 0) {
            $prepare->put("[#ErrMessage]", "Wrong item ID!");
            $this->write("error", $prepare);
            return;
        }

        $oItem = $dsItems->getRow(0);
        $title = $oItem->get("s_Title");
        $sourceName = $oItem->get("s_SourceName");

        $this->context->set("Page_Title", $title);
        $leftWidth = "25%";
        if ($this->context->IsMobile)
            $leftWidth = "20%";

        $idField = $doItem->getIdField();
        $redirectItem = CAT(
            (BLANK($this->context->Api) ? "" : $this->context->Site),
            Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/item/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_item&id=")),
            $oItem->get($idField));
        $prepare->put("[#RedirectLink]", $redirectItem);
        $prepare->put("[#LeftWidth]", $leftWidth);
        $prepare->put("[#Title]", Util::show($title));
        $prepare->put("[#InputTitle]", Util::safe($title));

        $redirectSource = CAT(
            (BLANK($this->context->Api) ? "" : $this->context->Site),
            Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/source/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_source&source=")),
            $sourceName
        );
        $prepare->put("[#RedirectSource]", $redirectSource);
        $prepare->put("[#SourceName]", $sourceName);
        $prepare->put("[#Date]", Util::showTime($oItem->get("d_Date")));
        $prepare->put("[#Creator]", $oItem->get("s_Creator"));
        $prepare->put("[#Description]", $oItem->containsKey("t_Description") ? Util::show($oItem->get("t_Description")) : "");
        $prepare->put("[#ItemID]", $oItem->get($idField));
        if ($this->context->contains("Name_Category")) $prepare->put("[#Category]", $oItem->get("s_Category"));
        if ($this->context->contains("Name_Custom1")) $prepare->put("[#Custom1]", $oItem->get("s_Custom1"));
        if ($this->context->contains("Name_Custom2")) $prepare->put("[#Custom2]", $oItem->get("s_Custom2"));

        if ($this->context->Lang == "ru" && !$this->context->IsMobile)
            $prepare->put("[#Share]", 1);

        $engine = $this->context->getEngine();

        if (Config::CACHE_PAGES)
            $prepare->put("[#Home]", Util::showFromCache($engine, $this->context->CacheFolder, "home", "Home", "p=home&from_view_item=1"));
        else
            $prepare->put("[#Home]", $engine->includeTemplate("Pages/Home"));

        $this->write("Pages/view_item", $prepare);
    }
}
