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

use Bula\Objects\ArrayList;
use Bula\Objects\Arrays;
use Bula\Objects\Hashtable;
use Bula\Objects\Regex;
use Bula\Objects\RegexOptions;

use Bula\Objects\TString;
use Bula\Objects\Strings;
use Bula\Model\DataSet;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/Regex.php");
require_once("Bula/Objects/RegexOptions.php");
require_once("Bula/Model/DataSet.php");

/**
 * Manipulating with items.
 */
class BOItem
{
    // Input fields
    /** Source name */
    private $source = null;
    /** RSS-item */
    private $item = null;

    /** Link to external item */
    public $link = null;
    /** Original title */
    public $fullTitle = null;
    /** Original description */
    public $fullDescription = null;

    // Output fields
    /** Final (processed) title */
    public $title = null;
    /** Final (processed) description */
    public $description = null;

    // Custom output fields
    /** Extracted creator (publisher) */
    public $creator = null;
    /** Extracted category */
    public $category = null;
    /** Extracted custom field 1 */
    public $custom1 = null;
    /** Extracted custom field 2 */
    public $custom2 = null;

    /**
     * Instantiate BOItem from given source and RSS-item.
     * @param TString $source Current processed source.
     * @param TString $item Current processed RSS-item from given source.
     */
    public function __construct($source, $item)
    {
        $this->initialize($source, $item);
    }

    /**
     * Initialize this BOItem.
     * @param Hashtable $source Current processed source.
     * @param Hashtable $item Current processed RSS-item from given source.
     */
    private function initialize($source, Hashtable $item)
    {
        $this->source = $source;
        $this->item = $item;

        $this->link = $item->get("link");

        // Pre-process full description & title
        // Trick to eliminate non-UTF-8 characters
        $this->fullTitle = Regex::replace($item->get("title"), "[\xF0-\xF7][\x80-\xBF]{3}", "");
        if ($item->containsKey("description") && !BLANK($item->get("description")))
            $this->fullDescription = Regex::replace($item->get("description"), "[\xF0-\xF7][\x80-\xBF]{3}", "");

        $this->preProcessLink();
    }

    /**
     * Pre-process link (just placeholder for now)
     */
    protected function preProcessLink()
    {}

    /**
     * Process description.
     */
    public function processDescription()
    {
        $BR = "\n";
        $title = Strings::removeTags($this->fullTitle);
        $title = $title->replace("&#", "[--amp--]");
        $title = $title->replace("#", "[sharp]");
        $title = $title->replace("[--amp--]", "&#");
        $title = $title->replace("&amp;", "&");
        $this->title = $title;

        if ($this->fullDescription == null)
            return;
        $description = $this->fullDescription;

        //TODO -- Fixes for FetchRSS feeds (parsed from Twitter) here...
        $description = $description->replace("&#160;", "");
        $description = $description->replace("&nbsp;", "");

        // Start -- Fixes and workarounds for some sources here...
        // End

        $hasP = Regex::isMatch($description, "<p[^>]*>");
        $hasBr = $description->indexOf("<br") != -1;
        $hasLi = $description->indexOf("<li") != -1;
        $hasDiv = $description->indexOf("<div") != -1;
        $includeTags = Strings::concat(
            "<br>",
            ($hasP ? "<p>" : null),
            ($hasLi ? "<ul><ol><li>" : null),
            ($hasDiv ? "<div>" : null)
        );

        $description = Strings::removeTags($description, $includeTags);

        if ($hasBr)
            $description = Regex::replace($description, "[ \t\r\n]*<br[ ]*[/]*>[ \t\r\n]*", $BR, RegexOptions::IgnoreCase);
        if ($hasLi) {
            $description = Regex::replace($description, "<ul[^>]*>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "<ol[^>]*>", "* ", RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "<li[^>]*>", "* ", RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</ul>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</ol>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</li>", $BR, RegexOptions::IgnoreCase);
        }
        if ($hasP) {
            $description = Regex::replace($description, "<p[^>]*>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</p>", $BR, RegexOptions::IgnoreCase);
        }
        if ($hasDiv) {
            $description = Regex::replace($description, "<div[^>]*>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</div>", $BR, RegexOptions::IgnoreCase);
        }

        // Process end-of-lines...
        while ($description->indexOf(" \n") != -1)
            $description = $description->replace(" \n", "\n");
        while ($description->indexOf("\n\n\n") != -1)
            $description = $description->replace("\n\n\n", "\n\n");
        $description = Regex::replace($description, "\n\n[ \t]*[+\-\*][^+\-\*][ \t]*", "\n* ");
        $description = Regex::replace($description, "[ \t]+", " ");

        $this->description = $description->trim();
    }

    /**
     * Process category (if any).
     */
    public function processCategory()
    {
        // Set or fix category from item
        $category = null;
        if (!BLANK($this->item->get("category")))
            $category = $this->preProcessCategory($this->item->get("category"));
        else {
            if (!BLANK($this->item->get("tags")))
                $category = $this->preProcessCategory($this->item->get("tags"));
            else
                $category = $this->extractCategory();
        }
        $this->category = $category;
    }

    /**
     * Pre-process category.
     * @param TString $categoryItem Input category.
     * @return TString Pre-processed category.
     */
    private function preProcessCategory($categoryItem)
    {
        // Pre-process category from $item["category"]

        // This is just sample - implement your own logic
        if (EQ($this->source, "something.com")) {
            // Fix categories from something.com
        }

        $category = null;
        if (!$categoryItem->isEmpty()) {
            $categoriesArr = $categoryItem->replace(",&,", " & ")->split(",");
            $categoriesNew = new ArrayList();
            for ($c = 0; $c < SIZE($categoriesArr); $c++) {
                $temp = $categoriesArr[$c];
                if (BLANK($temp->trim()))
                    continue;
                $temp = Strings::firstCharToUpper($temp);
                if ($category == null)
                    $category = $temp;
                else
                    $category->concat(CAT(", ", $temp));
            }
        }

        return $category;
    }

    /**
     * Extract category.
     * @return TString Resulting category.
     */
    private function extractCategory()
    {
        // Try to extract category from description body (if no $item["category"])

        $category = null;

        //TODO -- This is just sample - implement your own logic for extracting category
        //if (Config::$RssAllowed == null)
        //    $category = $this->source;

        return $category;
    }

    /**
     * Add standard categories (from DB) to current item.
     * @param DataSet $dsCategories DataSet with categories (pre-loaded from DB).
     * @param TString $lang Input language.
     */
    public function addStandardCategories($dsCategories, $lang)
    {
        //if (BLANK($this->description))
        //    return;

        $categoryTags = BLANK($this->category) ?
            Strings::emptyArray() : $this->category->split(",");
        for ($n1 = 0; $n1 < $dsCategories->getSize(); $n1++) {
            $oCategory = $dsCategories->getRow($n1);
            $rssAllowedKey = $oCategory->get("s_CatId");
            $name = $oCategory->get("s_Name");

            $filterValue = $oCategory->get("s_Filter");
            $filterChunks = Strings::split("~", $filterValue);
            $includeChunks = SIZE($filterChunks) > 0 ?
                Strings::split("|", $filterChunks[0]) : Strings::emptyArray();
            $excludeChunks = SIZE($filterChunks) > 1 ?
                Strings::split("|", $filterChunks[1]) : Strings::emptyArray();

            $includeFlag = false;
            for ($n2 = 0; $n2 < SIZE($includeChunks); $n2++) {
                $includeChunk = Regex::escape($includeChunks[$n2]);
                if (!BLANK($this->description) && Regex::isMatch($this->description, $includeChunk, RegexOptions::IgnoreCase))
                    $includeFlag |= true;
                if (Regex::isMatch($this->title, $includeChunk, RegexOptions::IgnoreCase))
                    $includeFlag |= true;
            }
            for ($n3 = 0; $n3 < SIZE($excludeChunks); $n3++) {
                $excludeChunk = Regex::escape($excludeChunks[$n3]);
                if (!BLANK($this->description) && Regex::isMatch($this->description, $excludeChunk, RegexOptions::IgnoreCase))
                    $includeFlag &= false;
                if (Regex::isMatch($this->title, $excludeChunk, RegexOptions::IgnoreCase))
                    $includeFlag &= false;
            }
            if ($includeFlag) {
                $categoryTags = ADD($categoryTags, $name);
             }
        }
        if (SIZE($categoryTags) == 0)
            return;

        //TODO
        //$uniqueCategories = $this->NormalizeList($categoryTags, $lang);
        //$category = TString::join(", ", $uniqueCategories);

        $this->category = Strings::join(", ", $categoryTags);
    }

    /**
     * Process creator (publisher, company etc).
     */
    public function processCreator()
    {
        // Extract creator from item (if it is not set yet)
        if ($this->creator == null) {
            if (!BLANK($this->item->get("company")))
                $this->creator = $this->item->get("company");
            else if (!BLANK($this->item->get("source")))
                $this->creator = $this->item->get("source");
            else if (!BLANK($this->item->get("dc"))) { //TODO implement [dc][creator]
                $temp = $this->item->get("dc");
                if (!BLANK($temp->get("creator")))
                    $this->creator = $temp->get("creator");
            }
        }
        if ($this->creator != null)
            $this->creator = Regex::replace($this->creator, "[ \t\r\n]+", " ");

        //TODO -- Implement your own logic for extracting creator here
    }

    /**
     * Generate URL title from item title.
     * @param Boolean $translit Whether to apply transliteration or not.
     * @return TString Resulting URL title.
     *
     * For example:
     * "Officials: Fireworks Spark Utah Wildfire, Evacuations"
     *    will become
     * "officials-fireworks-spark-utah-wildfire-evacuations"
     */
    public function getUrlTitle($translit= false)
    {
        $title = Strings::addSlashes($this->title);

        if ($translit)
            $title = Util::transliterateRusToLat($title);

        $title = Regex::replace($title, "\&amp\;", " and ");
        $title = Regex::replace($title, "[^A-Za-z0-9\-\. ]", " ");
        $title = Regex::replace($title, " +", " ");
        $title = $title->trim();
        $title = Regex::replace($title, "\.+", "-");
        $title = Regex::replace($title, " \- ", "-");
        $title = Regex::replace($title, " \. ", ".");
        $title = Regex::replace($title, "[ ]+", "-");
        $title = Regex::replace($title, "\-+", "-");
        $title = $title->trim("-")->toLowerCase();
        return $title;
    }
}
