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

use Bula\Objects\Response;
use Bula\Objects\Strings;

require_once("Bula/Objects/Response.php");
require_once("Bula/Objects/Strings.php");

require_once("Bula/Fetcher/Controller/RssBase.php");

/**
 * Main logic for generating RSS-feeds.
 */
class Rss extends RssBase
{

    public function writeErrorMessage($errorMessage)
    {
        Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
        Response::write("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n");
        Response::write(CAT("<data>", $errorMessage, "</data>"));
    }

    public function writeStart($source, $filterName, $pubDate)
    {
        $rssTitle = CAT(
            "Items for ", (BLANK($source) ? "ALL sources" : CAT("'", $source, "'")),
            (BLANK($filterName) ? null : CAT(" and filtered by '", $filterName, "'"))
        );
        $xmlContent = Strings::concat(
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
        Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
        Response::write("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n");
        Response::write($xmlContent->getValue());
        return $xmlContent;
    }

    public function writeEnd()
    {
        $xmlContent = Strings::concat(
            "</channel>\r\n",
            "</rss>\r\n");
        Response::write($xmlContent->getValue());
        Response::end("");
        return $xmlContent;
    }

    public function writeItem($args)
    {
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
        $itemContent = Util::formatString($xmlTemplate, $args);
        Response::write($itemContent->getValue());
        return $itemContent;
    }
}
