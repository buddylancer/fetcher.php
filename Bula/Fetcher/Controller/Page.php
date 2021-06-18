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

use Bula\Objects\DataRange;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Objects\TString;
use Bula\Objects\DateTimes;

require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/DataRange.php");

/**
 * Basic logic for generating Page block.
 */
abstract class Page
{
    /** Current context */
    protected $context = null;

    /**
     * Public default constructor.
     * @param Context $context Context instance.
     */
    public function __construct($context)
    {
        $this->context = $context;
        //echo "In Page constructor -- " . print_r($context, true);
    }

    /** Execute main logic for page block */
    abstract public function execute();

    /**
     * Merge template with variables and write to engine.
     * @param TString $template Template name.
     * @param DataRange $prepare Prepared variables.
     */
    public function write($template, $prepare)
    {
        $engine = $this->context->getEngine();
        $engine->write($engine->showTemplate($template, $prepare));
    }

    /**
     * Get link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @return TString Resulting link.
     */

    /**
     * Get link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @param TString $extraData Optional prefix.
     * @return TString Resulting link.
     */
    public function getLink($page, $ordinaryUrl, $fineUrl, $extraData= null)
    {
        if (!BLANK($this->context->Api))
            return $this->getAbsoluteLink($page, $ordinaryUrl, $fineUrl, $extraData);
        else
            return $this->getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData);
    }

    /**
     * Get relative link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @return TString Resulting relative link.
     */

    /**
     * Get relative link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @param TString $extraData Optional prefix.
     * @return TString Resulting relative link.
     */
     public function getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData= null)
    {
        $link = CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? $fineUrl : CAT($page, $this->quoteLink($ordinaryUrl))),
            $extraData);
        return $link;
    }

    /**
     * Get absolute link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @return TString Resulting absolute link.
     */

    /**
     * Get absolute link for the page.
     * @param TString $page Page to get link for.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @param TString $extraData Optional prefix.
     * @return TString Resulting absolute link.
     */
     public function getAbsoluteLink($page, $ordinaryUrl, $fineUrl, $extraData= null)
    {
        return CAT($this->context->Site, $this->getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData));
    }

    /**
     * Append info to a link.
     * @param TString $link Link to append info to.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @return TString Resulting link.
     */

    /**
     * Append info to a link.
     * @param TString $link Link to append info to.
     * @param TString $ordinaryUrl Url portion of full Url.
     * @param TString $fineUrl Url portion of fine Url.
     * @param TString $extraData Optional prefix.
     * @return TString Resulting link.
     */
    public function appendLink($link, $ordinaryUrl, $fineUrl, $extraData= null)
    {
        return CAT($link, ($this->context->FineUrls ? $fineUrl : $this->quoteLink($ordinaryUrl)), $extraData);
    }

    /**
     * Quote (escape special characters) a link.
     * @param TString $link Source link.
     * @return TString Target (quoted) link.
     */
    public function quoteLink($link)
    {
        return !BLANK($this->context->Api) && EQ(Config::API_FORMAT, "Xml") ? Util::safe($link) : $link;
    }
}
