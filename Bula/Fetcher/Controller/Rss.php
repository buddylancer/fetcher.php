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
        Response::write(CAT("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", EOL));
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
            "<channel>", EOL,
            //"<title>" . Config::SITE_NAME . "</title>", EOL,
            "<title>", $rssTitle, "</title>", EOL,
            "<link>", $this->context->Site, Config::TOP_DIR, "</link>", EOL,
            "<description>", $rssTitle, "</description>", EOL,
            ($this->context->Lang == "ru" ? "<language>ru-RU</language>\r\n" : "<language>en-US</language>"), EOL,
            "<pubDate>", $pubDate, "</pubDate>", EOL,
            "<lastBuildDate>", $pubDate, "</lastBuildDate>", EOL,
            "<generator>", Config::SITE_NAME, "</generator>", EOL
        );
        Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
        Response::write(CAT("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", EOL));
        Response::write($xmlContent->getValue());
        return $xmlContent;
    }

    public function writeEnd()
    {
        $xmlContent = Strings::concat(
            "</channel>", EOL,
            "</rss>", EOL);
        Response::write($xmlContent->getValue());
        Response::end("");
        return $xmlContent;
    }

    public function writeItem($args)
    {
        $xmlTemplate = Strings::concat(
            "<item>", EOL,
            "<title><![CDATA[{1}]]></title>", EOL,
            "<link>{0}</link>", EOL,
            "<pubDate>{4}</pubDate>", EOL,
            BLANK($args[5]) ? null : CAT("<description><![CDATA[{5}]]></description>", EOL),
            BLANK($args[6]) ? null : CAT("<category><![CDATA[{6}]]></category>", EOL),
            "<guid>{0}</guid>", EOL,
            "</item>", EOL
        );
        $itemContent = Util::formatString($xmlTemplate, $args);
        Response::write($itemContent->getValue());
        return $itemContent;
    }
}
