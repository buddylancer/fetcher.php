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
class ViewItem extends Page {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public ViewItem(Context context) : base(context) { }
    CS*/

    /**
     * Fast check of input query parameters.
     * @return Hashtable Parsed parameters (or null in case of any error).
     */
    public function check() {
        $Prepare = new Hashtable();
        if (!Request::contains("id")) {
            $Prepare->put("[#ErrMessage]", "Item ID is required!");
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return null;
        }
        $id = Request::get("id");
        if (!Request::isInteger($id)) {
            $Prepare->put("[#ErrMessage]", "Item ID must be positive integer!");
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return null;
        }

        $Pars = new Hashtable();
        $Pars->put("id", $id);
        return $Pars;
    }

    /** Execute main logic for View Item block. */
    public function execute() {
        $Pars = self::check();
        if ($Pars == null)
            return;

        $id = /*(TString)*/$Pars->get("id");

        $Prepare = new Hashtable();

        $doItem = new DOItem();
        $dsItems = $doItem->getById(INT($id));
        if ($dsItems == null || $dsItems->getSize() == 0) {
            $Prepare->put("[#ErrMessage]", "Wrong item ID!");
            $this->write("Bula/Fetcher/View/error.html", $Prepare);
            return;
        }

        $oItem = $dsItems->getRow(0);
        $title = $oItem->get("s_Title");
        $source_name = $oItem->get("s_SourceName");

        $this->context->set("Page_Title", $title);
        $left_width = "25%";
        if ($this->context->IsMobile)
            $left_width = "20%";

        $id_field = $doItem->getIdField();
        $redirect_item = CAT(Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/item/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_item&id=")),
            $oItem->get($id_field));
        $Prepare->put("[#RedirectLink]", $redirect_item);
        $Prepare->put("[#LeftWidth]", $left_width);
        $Prepare->put("[#Title]", Util::show($title));
        $Prepare->put("[#InputTitle]", Util::safe($title));

        $redirect_source = CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/source/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_source&source=")),
            $source_name
        );
        $Prepare->put("[#RedirectSource]", $redirect_source);
        $Prepare->put("[#SourceName]", $source_name);
        $Prepare->put("[#Date]", Util::showTime($oItem->get("d_Date")));
        $Prepare->put("[#Creator]", $oItem->get("s_Creator"));
        $Prepare->put("[#Description]", $oItem->containsKey("t_Description") ? Util::show($oItem->get("t_Description")) : "");
        $Prepare->put("[#ItemID]", $oItem->get($id_field));
        if ($this->context->contains("Name_Category")) $Prepare->put("[#Category]", $oItem->get("s_Category"));
        if ($this->context->contains("Name_Custom1")) $Prepare->put("[#Custom1]", $oItem->get("s_Custom1"));
        if ($this->context->contains("Name_Custom2")) $Prepare->put("[#Custom2]", $oItem->get("s_Custom2"));

        if ($this->context->Lang == "ru" && !$this->context->IsMobile)
            $Prepare->put("[#Share]", 1);

        $engine = $this->context->getEngine();

        if (Config::CACHE_PAGES)
            $Prepare->put("[#Home]", Util::showFromCache($engine, $this->context->CacheFolder, "home", "Home", "p=home&from_view_item=1"));
        else
            $Prepare->put("[#Home]", $engine->includeTemplate("Bula/Fetcher/Controller/Pages/Home"));

        $this->write("Bula/Fetcher/View/Pages/view_item.html", $Prepare);
    }
}
