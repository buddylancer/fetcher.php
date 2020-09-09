<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Fetcher\Config;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Objects\Regex;
use Bula\Objects\RegexOptions;

use Bula\Objects\Request;
use Bula\Objects\Response;

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
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Response.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/Hashtable.php");
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
 * Main logic for generating RSS-feeds.
 */
class Rss extends Page
{

    /**
     * Execute main logic for generating RSS-feeds.
     */
    public function execute()
    {
        Request::initialize();
        Request::extractAllVars();

        $errorMessage = new TString();

        // Check source
        $source = Request::get("source");
        if (!NUL($source)) {
            if (BLANK($source))
                $errorMessage->concat("Empty source!");
            else {
                $doSource = new DOSource();
                $oSource =
                    ARR(new Hashtable());
                if (!$doSource->checkSourceName($source, $oSource))
                    $errorMessage->concat(CAT("Incorrect source '", $source, "'!"));
            }
        }

        $anyFilter = false;
        if (Request::contains("code")) {
            if (EQ(Request::get("code"), Config::SECURITY_CODE))
                $anyFilter = true;
        }

        // Check filter
        $filter = null;
        $filterName = null;
        $doCategory = new DOCategory();
        $dsCategories = $doCategory->enumCategories();
        if ($dsCategories->getSize() > 0) {
            $filterName = Request::get("filter");
            if (!NUL($filterName)) {
                if (BLANK($filterName)) {
                    if ($errorMessage->length() > 0)
                        $errorMessage->concat(" ");
                    $errorMessage->concat("Empty filter!");
                }
                else {
                    $oCategory =
                        ARR(new Hashtable());
                    if ($doCategory->checkFilterName($filterName, $oCategory))
                        $filter = $oCategory[0]->get("s_Filter");
                    else {
                        if ($anyFilter)
                            $filter = $filterName;
                        else
                            $errorMessage->concat(CAT("Incorrect filter '", $filterName, "'!"));
                    }
                }
            }
        }

        // Check that parameters contain only 'source' or/and 'filter'
        $keys = Request::getKeys();
        while ($keys->moveNext()) {
            $key = $keys->current();
            if ($key != "source" && $key != "filter" && $key != "code" && $key != "count") {
                if ($errorMessage->length() > 0)
                    $errorMessage->concat(" ");
                $errorMessage->concat(CAT("Incorrect parameter '", $key, "'!"));
            }
        }

        if ($errorMessage->length() > 0) {
            Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
            Response::write("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n");
            Response::write(CAT("<data>", $errorMessage, "</data>"));
            return;
        }

        $fullTitle = false;
        if (Request::contains("title") && Request::get("title") == "full")
            $fullTitle = true;

        $count = Config::MAX_RSS_ITEMS;
        $countSet = false;
        if (Request::contains("count")) {
            if (INT(Request::get("count")) > 0) {
                $count = INT(Request::get("count"));
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
                Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
                $tempContent = Helper::readAllText($cachedFile);
                //Response::write($tempContent->substring(3)); //TODO -- BOM?
                Response::write($tempContent); //TODO -- BOM?
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

        $pubDate = DateTimes::format(Config::XML_DTS);
        $nowTime = DateTimes::getTime($pubDate);
        $fromDate = DateTimes::gmtFormat(Config::XML_DTS, $nowTime - 6*60*60);
        $dsItems = $doItem->enumItemsFromSource($fromDate, $source, $filter, $count);
        $current = 0;
        $itemsContent = new TString();
        for ($n = 0; $n < $dsItems->getSize(); $n++) {
            $oItem = $dsItems->getRow($n);
            $date = $oItem->get("d_Date");
            if (DateTimes::getTime($date) > $nowTime)
                continue;

            if ($current == 0)
                $pubDate = DateTimes::format(Config::XML_DTS, DateTimes::getTime($date));

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
                $link = CAT(
                    $this->context->Site, Config::TOP_DIR,
                    ($this->context->FineUrls ? "item/" : CAT(Config::INDEX_PAGE, "?p=view_item&amp;id=")),
                    $oItem->get($idField),
                    (BLANK($url) ? null : CAT(($this->context->FineUrls ? "/" : "&amp;title="), $url))
                );
            }

            $args = array(7);
            $args[0] = $link;
            $args[1] = $itemTitle;
            $args[2] = CAT($this->context->Site, Config::TOP_DIR, Config::ACTION_PAGE, "?p=do_redirect_source&amp;source=", $sourceName);
            $args[3] = $sourceName;
            $args[4] = DateTimes::format(Config::XML_DTS, DateTimes::getTime($date));
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

            $xmlTemplate = Strings::concat(
                "<item>\r\n",
                "<title><![CDATA[{1}]]></title>\r\n",
                "<link>{0}</link>\r\n",
                "<pubDate>{4}</pubDate>\r\n",
                BLANK($args[5]) ? null : "<description><![CDATA[{5}]]></description>\r\n",
                BLANK($args[6]) ? null : "<category><![CDATA[{6}]]></category>\r\n",
                "<guid>{0}</guid>\r\n",
                "</item>\r\n"
            );
            $itemsContent->concat(Util::formatString($xmlTemplate, $args));
            $current++;
        }

        $rssTitle = CAT(
            "Items for ", (BLANK($source) ? "ALL sources" : CAT("'", $source, "'")),
            (BLANK($filterName) ? null : CAT(" and filtered by '", $filterName, "'"))
        );
        $xmlContent = Strings::concat(
            "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n",
            "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\r\n",
            "<channel>\r\n",
            //"<title>" . Config::SITE_NAME . "</title>\r\n",
            "<title>", $rssTitle, "</title>\r\n",
            "<link>", $this->context->Site, Config::TOP_DIR, "</link>\r\n",
            "<description>", $rssTitle, "</description>\r\n",
            ($this->context->Lang == "ru" ? "<language>ru-RU</language>\r\n" : "<language>en-US</language>\r\n"),
            "<pubDate>", $pubDate, "</pubDate>\r\n",
            "<lastBuildDate>", $pubDate, "</lastBuildDate>\r\n",
            "<generator>", Config::SITE_NAME, "</generator>\r\n"
        );

        $xmlContent->concat($itemsContent);

        $xmlContent->concat(CAT(
            "</channel>\r\n",
            "</rss>\r\n"));

        // Save content to cache (if applicable)
        if (Config::CACHE_RSS && !$countSet)
        {
            Helper::testFileFolder($cachedFile);
            //Helper::writeText($cachedFile, Strings::concat("\xEF\xBB\xBF", $xmlContent));
            Helper::writeText($cachedFile, $xmlContent);
        }

        Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
        Response::write($xmlContent->getValue());

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }
}
