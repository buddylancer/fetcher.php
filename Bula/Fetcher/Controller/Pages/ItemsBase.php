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

use Bula\Objects\Hashtable;
use Bula\Objects\Regex;

use Bula\Fetcher\Config;
use Bula\Objects\Request;
use Bula\Objects\Strings;
use Bula\Objects\TString;
use Bula\Fetcher\Controller\Util;
use Bula\Fetcher\Controller\Engine;
use Bula\Fetcher\Controller\Page;

/**
 * Base controller for Items block.
 */
abstract class ItemsBase extends Page {

    /**
     * Check list from current query.
     * @return Boolean True - checked OK, False - error.
     */
    public function checkList() {
        if (Request::contains("list")) {
            if (!Request::isInteger(Request::get("list"))) {
                $Prepare = new Hashtable();
                $Prepare->put("[#ErrMessage]", "Incorrect list number!");
                $this->write("Bula/Fetcher/View/error.html", $Prepare);
                return false;
            }
        }
        else
            Request::set("list", "1");
        return true;
    }

    /**
     * Check source name from current query.
     * @return Boolean True - source exists, False - error.
     */
    public function checkSource() {
        $err_message = new TString();
        if (Request::contains("source")) {
            $source = Request::get("source");
            if (BLANK($source))
                $err_message->concat("Empty source name!<br/>");
            else if (!Request::isDomainName("source"))
                $err_message->concat("Incorrect source name!<br/>");
        }
        if ($err_message->isEmpty())
            return true;

        $Prepare = new Hashtable();
        $Prepare->put("[#ErrMessage]", $err_message);
        $this->write("Bula/Fetcher/View/error.html", $Prepare);
        return false;
    }

    /**
     * Fill Row from Item.
     * @param Hashtable $oItem Original Item.
     * @param TString $id_field Name of ID field.
     * @param Integer $count The number of inserted Row in HTML table.
     * @return Hashtable Resulting Row.
     */
    protected function fillItemRow(Hashtable $oItem, $id_field, $count) {
        $Row = new Hashtable();
        $item_id = INT($oItem->get($id_field));
        $url_title = $oItem->get("s_Url");
        $item_href = $this->context->ImmediateRedirect ? self::getRedirectItemLink($item_id, $url_title) :
                self::getViewItemLink($item_id, $url_title);
        $Row->put("[#Link]", $item_href);
        if (($count % 2) == 0)
            $Row->put("[#Shade]", "1");

        if (Config::SHOW_FROM)
            $Row->put("[#Show_From]", 1);
        $Row->put("[#Source]", $oItem->get("s_SourceName"));
        $Row->put("[#Title]", Util::show($oItem->get("s_Title")));

        if ($this->context->contains("Name_Category") && $oItem->containsKey("s_Category") && $oItem->get("s_Category") != "")
            $Row->put("[#Category]", $oItem->get("s_Category"));

        if ($this->context->contains("Name_Creator") && $oItem->containsKey("s_Creator") && $oItem->get("s_Creator") != "") {
            $s_Creator = $oItem->get("s_Creator");
            if ($s_Creator != null) {
                if ($s_Creator->indexOf("(") != -1)
                    $s_Creator = $s_Creator->replace("(", "<br/>(");
            }
            else
                $s_Creator = new TString(" "); //TODO -- "" doesn't works somehow, need to investigate
            $Row->put("[#Creator]", $s_Creator);
        }
        if ($this->context->contains("Name_Custom1") && $oItem->contains("s_Custom1") && $oItem->get("s_Custom1") != "")
            $Row->put("[#Custom1]", $oItem->get("s_Custom1"));
        if ($this->context->contains("Name_Custom2") && $oItem->contains("s_Custom2") && $oItem->get("s_Custom2") != "")
            $Row->put("[#Custom2]", $oItem->get("s_Custom2"));

        $d_Date = Util::showTime($oItem->get("d_Date"));
        if ($this->context->IsMobile)
            $d_Date = Strings::replace("-", " ", $d_Date);
        else
            $d_Date = Strings::replaceFirst(" ", "<br/>", $d_Date);
        $Row->put("[#Date]", $d_Date);
        return $Row;
    }

    /**
     * Get link for redirecting to external item.
     * @param TString $item_id Item ID.
     * @param TString $url_title Normalized title (to include in the link).
     * @return TString Resulting external link.
     */
    public function getRedirectItemLink($item_id, $url_title = null) {
        return CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/item/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_item&id=")), $item_id,
            ($url_title != null ? CAT($this->context->FineUrls ? "/" : "&title=", $url_title) : null)
        );
    }

    /**
     * Get link for redirecting to the item (internally).
     * @param TString $item_id Item ID.
     * @param TString $url_title Normalized title (to include in the link).
     * @return TString Resulting internal link.
     */
    public function getViewItemLink($item_id, $url_title = null) {
        return CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? "item/" : CAT(Config::INDEX_PAGE, "?p=view_item&id=")), $item_id,
            ($url_title != null ? CAT($this->context->FineUrls ? "/" : "&title=", $url_title) : null)
        );
    }

    /**
     * Get internal link to the page.
     * @param TString $list_no Page no.
     * @return TString Resulting internal link to the page.
     */
    protected function getPageLink($list_no) {
        $href = CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ?
                "items" : CAT(Config::INDEX_PAGE, "?p=items")),
            (BLANK(Request::get("source")) ? null :
                CAT(($this->context->FineUrls ? "/source/" : "&amp;source="), Request::get("source"))),
            (!$this->context->contains("filter") || BLANK($this->context->get("filter")) ? null :
                CAT(($this->context->FineUrls ? "/filter/" : "&amp;filter="), $this->context->get("filter"))),
            ($list_no == 1 ? null :
                CAT(($this->context->FineUrls ? "/list/" : "&list="), $list_no))
        );
        return $href;
    }

    //abstract function execute();
}
