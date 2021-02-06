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
abstract class ItemsBase extends Page
{

    /**
     * Check list from current query.
     * @return Boolean True - checked OK, False - error.
     */
    public function checkList()
    {
        if (Request::contains("list")) {
            if (!Request::isInteger(Request::get("list"))) {
                $prepare = new Hashtable();
                $prepare->put("[#ErrMessage]", "Incorrect list number!");
                $this->write("Bula/Fetcher/View/error.html", $prepare);
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
    public function checkSource()
    {
        $errMessage = new TString();
        if (Request::contains("source")) {
            $source = Request::get("source");
            if (BLANK($source))
                $errMessage->concat("Empty source name!<br/>");
            else if (!Request::isDomainName("source"))
                $errMessage->concat("Incorrect source name!<br/>");
        }
        if ($errMessage->isEmpty())
            return true;

        $prepare = new Hashtable();
        $prepare->put("[#ErrMessage]", $errMessage);
        $this->write("Bula/Fetcher/View/error.html", $prepare);
        return false;
    }

    /**
     * Fill Row from Item.
     * @param Hashtable $oItem Original Item.
     * @param TString $idField Name of ID field.
     * @param Integer $count The number of inserted Row in HTML table.
     * @return Hashtable Resulting Row.
     */
    protected function fillItemRow(Hashtable $oItem, $idField, $count)
    {
        $row = new Hashtable();
        $itemId = INT($oItem->get($idField));
        $urlTitle = $oItem->get("s_Url");
        $itemHref = $this->context->ImmediateRedirect ? self::getRedirectItemLink($itemId, $urlTitle) :
                self::getViewItemLink($itemId, $urlTitle);
        $row->put("[#Link]", $itemHref);
        if (($count % 2) == 0)
            $row->put("[#Shade]", "1");

        if (Config::SHOW_FROM)
            $row->put("[#Show_From]", 1);
        $row->put("[#Source]", $oItem->get("s_SourceName"));
        $row->put("[#Title]", Util::show($oItem->get("s_Title")));

        if ($this->context->contains("Name_Category") && $oItem->containsKey("s_Category") && $oItem->get("s_Category") != "")
            $row->put("[#Category]", $oItem->get("s_Category"));

        if ($this->context->contains("Name_Creator") && $oItem->containsKey("s_Creator") && $oItem->get("s_Creator") != "") {
            $s_Creator = $oItem->get("s_Creator");
            if ($s_Creator != null) {
                if ($s_Creator->indexOf("(") != -1)
                    $s_Creator = $s_Creator->replace("(", "<br/>(");
            }
            else
                $s_Creator = new TString(" "); //TODO -- "" doesn't works somehow, need to investigate
            $row->put("[#Creator]", $s_Creator);
        }
        if ($this->context->contains("Name_Custom1") && $oItem->contains("s_Custom1") && $oItem->get("s_Custom1") != "")
            $row->put("[#Custom1]", $oItem->get("s_Custom1"));
        if ($this->context->contains("Name_Custom2") && $oItem->contains("s_Custom2") && $oItem->get("s_Custom2") != "")
            $row->put("[#Custom2]", $oItem->get("s_Custom2"));

        $d_Date = Util::showTime($oItem->get("d_Date"));
        if ($this->context->IsMobile)
            $d_Date = Strings::replace("-", " ", $d_Date);
        else
            $d_Date = Strings::replaceFirst(" ", "<br/>", $d_Date);
        $row->put("[#Date]", $d_Date);
        return $row;
    }

    /**
     * Get link for redirecting to external item.
     * @param TString $itemId Item ID.
     * @param TString $urlTitle Normalized title (to include in the link).
     * @return TString Resulting external link.
     */
    public function getRedirectItemLink($itemId, $urlTitle = null)
    {
        return CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? "redirect/item/" : CAT(Config::ACTION_PAGE, "?p=do_redirect_item&id=")), $itemId,
            ($urlTitle != null ? CAT($this->context->FineUrls ? "/" : "&title=", $urlTitle) : null)
        );
    }

    /**
     * Get link for redirecting to the item (internally).
     * @param TString $itemId Item ID.
     * @param TString $urlTitle Normalized title (to include in the link).
     * @return TString Resulting internal link.
     */
    public function getViewItemLink($itemId, $urlTitle = null)
    {
        return CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? "item/" : CAT(Config::INDEX_PAGE, "?p=view_item&id=")), $itemId,
            ($urlTitle != null ? CAT($this->context->FineUrls ? "/" : "&title=", $urlTitle) : null)
        );
    }

    /**
     * Get internal link to the page.
     * @param TString $listNo Page no.
     * @return TString Resulting internal link to the page.
     */
    protected function getPageLink($listNo)
    {
        $href = CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ?
                "items" : CAT(Config::INDEX_PAGE, "?p=items")),
            (BLANK(Request::get("source")) ? null :
                CAT(($this->context->FineUrls ? "/source/" : "&amp;source="), Request::get("source"))),
            (!$this->context->contains("filter") || BLANK($this->context->get("filter")) ? null :
                CAT(($this->context->FineUrls ? "/filter/" : "&amp;filter="), $this->context->get("filter"))),
            ($listNo == 1 ? null :
                CAT(($this->context->FineUrls ? "/list/" : "&list="), $listNo))
        );
        return $href;
    }

    //abstract function execute();
}
