<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;

use Bula\Objects\TArrayList;
use Bula\Objects\TEnumerator;
use Bula\Objects\THashtable;
use Bula\Objects\Regex;
use Bula\Objects\RegexOptions;

use Bula\Objects\TRequest;
use Bula\Objects\TResponse;

use Bula\Objects\DateTimes;
use Bula\Objects\Helper;
use Bula\Objects\TString;
use Bula\Objects\Strings;

use Bula\Model\DBConfig;
use Bula\Model\DataSet;

use Bula\Fetcher\Model\DOCategory;
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Model\DOSource;

use Bula\Fetcher\Controller\Util;
use Bula\Fetcher\Controller\Page;

require_once("Bula/Meta.php");
require_once("Bula/Objects/TRequest.php");
require_once("Bula/Objects/TResponse.php");
require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/Regex.php");
require_once("Bula/Objects/RegexOptions.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Fetcher/Model/DOCategory.php");
require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Model/DOSource.php");
require_once("Bula/Fetcher/Controller/Util.php");
require_once("Bula/Fetcher/Controller/Page.php");

/**
 * Main logic for generating RSS-feeds and REST API responses.
 */
abstract class RssBase extends Page
{

    /**
     * Execute main logic for generating RSS-feeds.
     */
    public function execute()
    {
        //$this->context->Request->initialize();
        $this->context->Request->extractAllVars();

        $errorMessage = new TString();

        // Check source
        $source = $this->context->Request->get("source");
        if (!NUL($source)) {
            if (BLANK($source))
                $errorMessage->concat("Empty source!");
            else {
                $doSource = new DOSource();
                $oSource =
                    ARR(new THashtable());
                if (!$doSource->checkSourceName($source, $oSource))
                    $errorMessage->concat(CAT("Incorrect source '", $source, "'!"));
            }
        }

        $anyFilter = false;
        if ($this->context->Request->contains("code")) {
            if (EQ($this->context->Request->get("code"), Config::SECURITY_CODE))
                $anyFilter = true;
        }

        // Check filter
        $filter = null;
        $filterName = null;
        $doCategory = new DOCategory();
        $dsCategories = $doCategory->enumCategories();
        if ($dsCategories->getSize() > 0) {
            $filterName = $this->context->Request->get("filter");
            if (!NUL($filterName)) {
                if (BLANK($filterName)) {
                    if ($errorMessage->length() > 0)
                        $errorMessage->concat(" ");
                    $errorMessage->concat("Empty filter!");
                }
                else {
                    $oCategory =
                        ARR(new THashtable());
                    if ($doCategory->checkFilterName($filterName, $oCategory))
                        $filter = $oCategory[0]->get("s_Filter");
                    else {
                        if ($anyFilter)
                            $filter = $filterName;
                        else {
                            if ($errorMessage->length() > 0)
                                $errorMessage->concat(" ");
                            $errorMessage->concat(CAT("Incorrect filter '", $filterName, "'!"));
                        }
                    }
                }
            }
        }

        // Check that parameters contain only 'source' or/and 'filter'
        $keys = $this->context->Request->getKeys();
        while ($keys->moveNext()) {
            $key = $keys->getCurrent();
            if (EQ($key, "source") || EQ($key, "filter") || EQ($key, "code") || EQ($key, "count")) {
                //OK
            }
            else {
                //Not OK
                if ($errorMessage->length() > 0)
                    $errorMessage->concat(" ");
                $errorMessage->concat(CAT("Incorrect parameter '", $key, "'!"));
            }
        }

        if ($errorMessage->length() > 0) {
            $this->writeErrorMessage($errorMessage);
            return;
        }

        $fullTitle = false;
        if ($this->context->Request->contains("title") && $this->context->Request->get("title") == "full")
            $fullTitle = true;

        $count = Config::MAX_RSS_ITEMS;
        $countSet = false;
        if ($this->context->Request->contains("count")) {
            if (INT($this->context->Request->get("count")) > 0) {
                $count = INT($this->context->Request->get("count"));
                if ($count < Config::MIN_RSS_ITEMS)
                    $count = Config::MIN_RSS_ITEMS;
                if ($count > Config::MAX_RSS_ITEMS)
                    $count = Config::MAX_RSS_ITEMS;
                $countSet = true;
            }
        }

        // Get content from cache (if enabled and cache data exists)
        $cachedFile = new TString();
        if (Config::CACHE_RSS && !$countSet) {
            $cachedFile = Strings::concat(
                $this->context->RssFolder, "/rss",
                (BLANK($source) ? null : CAT("-s=", $source)),
                (BLANK($filterName) ? null : CAT("-f=", $filterName)),
                ($fullTitle ? "-full" : null), ".xml");
            if (Helper::fileExists($cachedFile)) {
                $this->context->Response->writeHeader("Content-type", "text/xml; charset=UTF-8");
                $tempContent = Helper::readAllText($cachedFile);
                //$this->context->Response->write($tempContent->substring(3)); //TODO -- BOM?
                $this->context->Response->write($tempContent); //TODO -- BOM?
                return;
            }
        }

        $doItem = new DOItem();

        // 0 - item url
        // 1 - item title
        // 2 - marketplace url
        // 3 - marketplace name
        // 4 - date
        // 5 - description
        // 6 - category

        $pubDate = DateTimes::format(DateTimes::XML_DTS);
        $nowDate = DateTimes::format(DateTimes::SQL_DTS);
        $nowTime = DateTimes::getTime($nowDate);
        $fromDate = DateTimes::gmtFormat(DateTimes::SQL_DTS, $nowTime - 6*60*60);
        $dsItems = $doItem->enumItemsFromSource($fromDate, $source, $filter, $count);
        $current = 0;

        $contentToCache = "";
        if ($dsItems->getSize() == 0)
            $contentToCache = $this->writeStart($source, $filterName, $pubDate);

        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $date = $oItem->get("d_Date");
            if (DateTimes::getTime($date) > $nowTime)
                continue;

            if ($current == 0) {
                // Get puDate from the first item and write starting block
                $pubDate = DateTimes::format(DateTimes::XML_DTS, DateTimes::getTime($date));
                $contentToCache = $this->writeStart($source, $filterName, $pubDate);
            }

            $category = $this->context->contains("Name_Category") ? $oItem->get("s_Category") : null;
            $creator = $this->context->contains("Name_Creator") ? $oItem->get("s_Creator") : null;
            $custom1 = $this->context->contains("Name_Custom1") ? $oItem->get("s_Custom1") : null;
            $custom2 = $this->context->contains("Name_Custom2") ? $oItem->get("s_Custom2") : null;

            $sourceName = $oItem->get("s_SourceName");
            $description = $oItem->get("t_Description");
            if (!BLANK($description)) {
                $description = Regex::replace($description, "<br/>", " ", RegexOptions::IgnoreCase);
                $description = Regex::replace($description, "&nbsp;", " ");
                $description = Regex::replace($description, "[ \r\n\t]+", " ");
                if ($description->length() > 512) {
                    $description = $description->substring(0, 511);
                    $lastSpaceIndex = $description->lastIndexOf(" ");
                    $description = Strings::concat($description->substring(0, $lastSpaceIndex), " ...");
                }
                //$utfIsValid = mb_check_encoding($description->getValue(), "UTF-8");
                //if ($utfIsValid == false)
                //    $description = new TString(); //TODO
            }
            $itemTitle = CAT(
                ($fullTitle == true && !BLANK($custom2) ? CAT($custom2, " | ") : null),
                Strings::removeTags(Strings::stripSlashes($oItem->get("s_Title"))),
                ($fullTitle == true ? CAT(" [", $sourceName, "]") : null)
            );

            $link = null;
            if ($this->context->ImmediateRedirect)
                $link = $oItem->get("s_Link");
            else {
                $url = $oItem->get("s_Url");
                $idField = $doItem->getIdField();
                $link = $this->getAbsoluteLink(Config::INDEX_PAGE, "?p=view_item&amp;id=", "item/", $oItem->get($idField));
                if (!BLANK($url))
                    $link = $this->appendLink($link, "&amp;title=", "/", $url);
            }

            $args = array(7);
            $args[0] = $link;
            $args[1] = $itemTitle;
            $args[2] = $this->getAbsoluteLink(Config::ACTION_PAGE, "?p=do_redirect_source&amp;source=", "redirect/source/", $sourceName);
            $args[3] = $sourceName;
            $args[4] = DateTimes::format(DateTimes::XML_DTS, DateTimes::getTime($date));
            $additional = CAT(
                (BLANK($creator) ? null : CAT($this->context->get("Name_Creator"), ": ", $creator, "<br/>")),
                (BLANK($category) ? null : CAT($this->context->get("Name_Categories"), ": ", $category, "<br/>")),
                (BLANK($custom2) ? null : CAT($this->context->get("Name_Custom2"), ": ", $custom2, "<br/>")),
                (BLANK($custom1) ? null : CAT($this->context->get("Name_Custom1"), ": ", $custom1, "<br/>"))
            );
            $extendedDescription = null;
            if (!BLANK($description)) {
                if (BLANK($additional))
                    $extendedDescription = $description;
                else
                    $extendedDescription = CAT($additional, "<br/>", $description);
            }
            else if (!BLANK($additional))
                $extendedDescription = $additional;
            $args[5] = $extendedDescription;
            $args[6] = $category;

            $itemContent = $this->writeItem($args);
            if (!BLANK($itemContent))
                $contentToCache->concat($itemContent);

            $current++;
        }

        $endContent = $this->writeEnd();
        if (!BLANK($endContent))
            $contentToCache->concat($endContent);

        // Save content to cache (if applicable)
        if (Config::CACHE_RSS && !$countSet) {
            Helper::testFileFolder($cachedFile);
            //Helper::writeText($cachedFile, Strings::concat("\xEF\xBB\xBF", $xmlContent));
            Helper::writeText($cachedFile, $contentToCache);
        }
        $this->context->Response->writeHeader("Content-type", "text/xml; charset=UTF-8");
        $this->context->Response->write($contentToCache); //TODO -- BOM?

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }

    /**
     * Write error message.
     * @param TString $errorMessage Error message.
     */
    abstract function writeErrorMessage($errorMessage);

    /**
     * Write start block (header) of an RSS-feed.
     * @param TString $source Source selected (or empty).
     * @param TString $filterName Filter name selected (or empty).
     * @param TString $pubDate Date shown in the header.
     */
    abstract function writeStart($source, $filterName, $pubDate);

    /**
     * Write end block of an RSS-feed.
     */
    abstract function writeEnd();

    /**
     * Write RSS-feed item.
     * @param Object[] $args Parameters to fill an item.
     */
    abstract function writeItem($args);
}
