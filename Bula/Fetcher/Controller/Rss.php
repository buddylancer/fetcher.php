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

use Bula\Objects\TResponse;
use Bula\Objects\Strings;

require_once("Bula/Objects/TResponse.php");
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
     * @param TString $category RSS-feed 'filtered by' category.
     * @param TString $pubDate Publication date.
     * @return TString Resulting XML-content of starting block.
     */
    public function writeStart($source, $category, $pubDate)
    {
        $rssTitle = CAT(
            "Items for ", (BLANK($source) ? "ALL sources" : CAT("'", $source, "'")),
            (BLANK($category) ? null : CAT(" and filtered by '", $category, "'"))
        );
        $xmlContent = Strings::concat(
            "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", EOL,
            "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">", EOL,
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
        return $itemContent;
    }
}
