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

use Bula\Objects\Hashtable;

use Bula\Fetcher\Config;
use Bula\Objects\TString;
use Bula\Objects\DateTimes;

require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Hashtable.php");

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
     * @param Hashtable $prepare Prepared variables.
     */
    public function write($template, $prepare)
    {
        $engine = $this->context->getEngine();
        $engine->write($engine->showTemplate($template, $prepare));
    }

    public function getLink($page, $ordinaryUrl, $fineUrl, $extraData = null)
    {
        if (!BLANK($this->context->Api))
            return $this->getAbsoluteLink($page, $ordinaryUrl, $fineUrl, $extraData);
        else
            return $this->getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData);
    }

    public function getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData = null)
    {
        $link = CAT(
            Config::TOP_DIR,
            ($this->context->FineUrls ? $fineUrl : CAT($page, $this->quoteLink($ordinaryUrl))),
            $extraData);
        return $link;
    }

    public function getAbsoluteLink($page, $ordinaryUrl, $fineUrl, $extraData = null)
    {
        return CAT($this->context->Site, $this->getRelativeLink($page, $ordinaryUrl, $fineUrl, $extraData));
    }

    public function appendLink($link, $ordinaryUrl, $fineUrl, $extraData = null)
    {
        return CAT($link, ($this->context->FineUrls ? $fineUrl : $this->quoteLink($ordinaryUrl)), $extraData);
    }

    public function quoteLink($link)
    {
        return !BLANK($this->context->Api) && EQ(Config::API_FORMAT, "Xml") ? Util::safe($link) : $link;
    }
}
