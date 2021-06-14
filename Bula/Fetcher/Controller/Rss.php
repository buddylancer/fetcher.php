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

    /**
     * Write error message.
     * @param TString $errorMessage Error message.
     */
    public function writeErrorMessage($errorMessage)
    {
        $this->context->Response->writeHeader("Content-type", "text/xml; charset=UTF-8");
        $this->context->Response->write(CAT("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", EOL));
        $this->context->Response->write(CAT("<data>", $errorMessage, "</data>"));
    }

    /**
     * Write starting block of RSS-feed.
     * @param TString $source RSS-feed source name.
     * @param TString $filterName RSS-feed 'filtered by' value.
     * @param TString $pubDate Publication date.
     * @return TString Resulting XML-content of starting block.
     */
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
        $this->context->Response->writeHeader("Content-type", "text/xml; charset=UTF-8");
        $this->context->Response->write(CAT("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", EOL));
        $this->context->Response->write($xmlContent->getValue());
        return $xmlContent;
    }

    /**
     * Write ending block of RSS-feed.
     */
    public function writeEnd()
    {
        $xmlContent = Strings::concat(
            "</channel>", EOL,
            "</rss>", EOL);
        $this->context->Response->write($xmlContent->getValue());
        $this->context->Response->end();
        return $xmlContent;
    }

    /**
     * Write an item of RSS-feed.
     * @param Object[] $args Array of item parameters.
     * @return TString Resulting XML-content of an item.
     */
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
        $this->context->Response->write($itemContent->getValue());
        return $itemContent;
    }
}
