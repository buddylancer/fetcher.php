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

use Bula\Objects\DataRange;
use Bula\Objects\Regex;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
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
        if ($this->context->Request->contains("list")) {
            if (!Request::isInteger($this->context->Request->get("list"))) {
                $prepare = new DataRange();
                $prepare->put("[#ErrMessage]", "Incorrect list number!");
                $this->write("error", $prepare);
                return false;
            }
        }
        else
            $this->context->Request->set("list", "1");
        return true;
    }

    /**
     * Check source name from current query.
     * @return Boolean True - source exists, False - error.
     */
    public function checkSource()
    {
        $errMessage = new TString();
        if ($this->context->Request->contains("source")) {
            $source = $this->context->Request->get("source");
            if (BLANK($source))
                $errMessage->concat("Empty source name!<br/>");
            else if (!Request::isDomainName("source"))
                $errMessage->concat("Incorrect source name!<br/>");
        }
        if ($errMessage->isEmpty())
            return true;

        $prepare = new DataRange();
        $prepare->put("[#ErrMessage]", $errMessage);
        $this->write("error", $prepare);
        return false;
    }

    /**
     * Fill Row from Item.
     * @param DataRange $oItem Original Item.
     * @param TString $idField Name of ID field.
     * @param Integer $count The number of inserted Row in HTML table.
     * @return DataRange Resulting Row.
     */
    protected function fillItemRow(DataRange $oItem, $idField, $count)
    {
        $row = new DataRange();
        $itemId = INT($oItem->get($idField));
        $urlTitle = $oItem->get("s_Url");
        $itemHref = $this->context->ImmediateRedirect ?
                self::getRedirectItemLink($itemId, $urlTitle) :
                self::getViewItemLink($itemId, $urlTitle);
        $row->put("[#Link]", $itemHref);
        if (($count % 2) == 0)
            $row->put("[#Shade]", "1");

        if (Config::SHOW_FROM)
            $row->put("[#Show_From]", 1);
        if (Config::SHOW_IMAGES)
            $row->put("[#Show_Images]", 1);
        $sourceName = $oItem->get("s_SourceName");
        $row->put("[#SourceName]", $sourceName);
        $row->put("[#ExtImages]", Config::EXT_IMAGES);
        $row->put("[#Title]", Util::show($oItem->get("s_Title")));
        $row->put("[#SourceLink]", $this->getLink(Config::INDEX_PAGE, "?p=items&source=", "items/source/", $sourceName));

        if ($this->context->contains("Name_Category") && $oItem->containsKey("s_Category") && !NUL($oItem->get("s_Category")))
            $row->put("[#Category]", $oItem->get("s_Category"));

        if ($this->context->contains("Name_Creator") && $oItem->containsKey("s_Creator") && !NUL($oItem->get("s_Creator"))) {
            $s_Creator = $oItem->get("s_Creator");
            if ($s_Creator != null) {
                if ($s_Creator->indexOf("(") != -1)
                    $s_Creator = $s_Creator->replace("(", "<br/>(");
            }
            else
                $s_Creator = new TString(" "); //TODO -- "" doesn't works somehow, need to investigate
            $row->put("[#Creator]", $s_Creator);
        }
        if ($this->context->contains("Name_Custom1") && $oItem->contains("s_Custom1") && !NUL($oItem->get("s_Custom1")))
            $row->put("[#Custom1]", $oItem->get("s_Custom1"));
        if ($this->context->contains("Name_Custom2") && $oItem->contains("s_Custom2") && !NUL($oItem->get("s_Custom2")))
            $row->put("[#Custom2]", $oItem->get("s_Custom2"));

        $d_Date = Util::showTime($oItem->get("d_Date"));
        if ($this->context->IsMobile)
            $d_Date = Strings::replace("-", " ", $d_Date);
        else {
            if (BLANK($this->context->Api))
                $d_Date = Strings::replaceFirst(" ", "<br/>", $d_Date);
        }
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
        $link = $this->getLink(Config::ACTION_PAGE, "?p=do_redirect_item&id=", "redirect/item/", $itemId);
        if (!BLANK($urlTitle))
            $link = $this->appendLink($link, "&title=", "/", $urlTitle);
        return $link;
    }

    /**
     * Get link for redirecting to the item (internally).
     * @param TString $itemId Item ID.
     * @param TString $urlTitle Normalized title (to include in the link).
     * @return TString Resulting internal link.
     */
    public function getViewItemLink($itemId, $urlTitle = null)
    {
        $link = $this->getLink(Config::INDEX_PAGE, "?p=view_item&id=", "item/", $itemId);
        if (!BLANK($urlTitle))
            $link = $this->appendLink($link, "&title=", "/", $urlTitle);
        return $link;
    }

    /**
     * Get internal link to the page.
     * @param TString $listNo Page no.
     * @return TString Resulting internal link to the page.
     */
    protected function getPageLink($listNo)
    {
        $link = $this->getLink(Config::INDEX_PAGE, "?p=items", "items");
        if ($this->context->Request->contains("source") && !BLANK($this->context->Request->get("source")))
            $link = $this->appendLink($link, "&source=", "/source/", $this->context->Request->get("source"));
        if ($this->context->Request->contains("filter") && !BLANK($this->context->Request->get("filter")))
            $link = $this->appendLink($link, "&amp;filter=", "/filter/", $this->context->Request->get("filter"));
        if ($listNo > 1)
            $link = $this->appendLink($link, "&list=", "/list/", $listNo);
        return $link;
    }

    //abstract function execute();
}
