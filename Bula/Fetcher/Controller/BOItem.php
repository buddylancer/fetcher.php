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
class BOItem {
    // Input fields
    /** Source name */
    private $source = null;
    /** RSS-item */
    private $item = null;

    /** Link to external item */
    public $link = null;
    /** Original title */
    public $full_title = null;
    /** Original description */
    public $full_description = null;

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
    public function __construct($source, $item) {
        $this->initialize($source, $item);
    }

    /**
     * Initialize this BOItem.
     * @param Hashtable $source Current processed source.
     * @param Hashtable $item Current processed RSS-item from given source.
     */
    private function initialize($source, Hashtable $item) {
        $this->source = $source;
        $this->item = $item;

        $this->link = /*(TString)*/$item->get("link");

        // Pre-process full description & title
        // Trick to eliminate non-UTF-8 characters
        $this->full_title = Regex::replace(/*(TString)*/$item->get("title"), "[\xF0-\xF7][\x80-\xBF]{3}", "");
        if ($item->containsKey("description") && !BLANK($item->get("description")))
            $this->full_description = Regex::replace(/*(TString)*/$item->get("description"), "[\xF0-\xF7][\x80-\xBF]{3}", "");

        $this->preProcessLink();
    }

    /**
     * Pre-process link (just placeholder for now)
     */
    protected function preProcessLink() {}

    /**
     * Process description.
     */
    public function processDescription() {
        $BR = "\n";
        $title = Strings::removeTags($this->full_title);
        $title = $title->replace("&#", "[--amp--]");
        $title = $title->replace("#", "[sharp]");
        $title = $title->replace("[--amp--]", "&#");
        $title = $title->replace("&amp;", "&");
        $this->title = $title;

        if ($this->full_description == null)
            return;
        $description = $this->full_description;

        //TODO -- Fixes for FetchRSS feeds (parsed from Twitter) here...
        $description = $description->replace("&#160;", "");
        $description = $description->replace("&nbsp;", "");

        // Start -- Fixes and workarounds for some sources here...
        // End

        $has_p = Regex::isMatch($description, "<p[^>]*>");
        $has_br = $description->indexOf("<br") != -1;
        $has_li = $description->indexOf("<li") != -1;
        $has_div = $description->indexOf("<div") != -1;
        $include_tags = Strings::concat(
            "<br>",
            ($has_p ? "<p>" : null),
            ($has_li ? "<ul><ol><li>" : null),
            ($has_div ? "<div>" : null)
        );

        $description = Strings::removeTags($description, $include_tags);

        if ($has_br)
            $description = Regex::replace($description, "[ \t\r\n]*<br[ ]*[/]*>[ \t\r\n]*", $BR, RegexOptions::IgnoreCase);
        if ($has_li) {
            $description = Regex::replace($description, "<ul[^>]*>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "<ol[^>]*>", "* ", RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "<li[^>]*>", "* ", RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</ul>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</ol>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</li>", $BR, RegexOptions::IgnoreCase);
        }
        if ($has_p) {
            $description = Regex::replace($description, "<p[^>]*>", $BR, RegexOptions::IgnoreCase);
            $description = Regex::replace($description, "</p>", $BR, RegexOptions::IgnoreCase);
        }
        if ($has_div) {
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
    public function processCategory() {
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
     * @param TString $category_item Input category.
     * @return TString Pre-processed category.
     */
    private function preProcessCategory($category_item) {
        // Pre-process category from $item["category"]

        // This is just sample - implement your own logic
        if (EQ($this->source, "something.com")) {
            // Fix categories from something.com
        }

        $category = null;
        if (!$category_item->isEmpty()) {
            $categories_arr = $category_item->replace(",&,", " & ")->split(",");
            $categories_new = new ArrayList();
            for ($c = 0; $c < SIZE($categories_arr); $c++) {
                $temp = $categories_arr[$c];
                if (!BLANK($temp))
                    $categories_new->add($temp);
            }
            $category = Strings::join(", ", /*(TString[])*/$categories_new->toArray());
        }

        return $category;
    }

    /**
     * Extract category.
     * @return TString Resulting category.
     */
    private function extractCategory() {
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
    public function addStandardCategories($dsCategories, $lang) {
        //if (BLANK($this->description))
        //    return;

        $category_tags = BLANK($this->category) ?
            Strings::emptyArray() : $this->category->split(",");
        for ($n1 = 0; $n1 < $dsCategories->getSize(); $n1++) {
            $oCategory = $dsCategories->getRow($n1);
            $rss_allowed_key = $oCategory->get("s_CatId");
            $name = $oCategory->get("s_Name");

            $filter_value = $oCategory->get("s_Filter");
            $filter_chunks = Strings::split("~", $filter_value);
            $include_chunks = SIZE($filter_chunks) > 0 ?
                Strings::split("|", $filter_chunks[0]) : Strings::emptyArray();
            $exclude_chunks = SIZE($filter_chunks) > 1 ?
                Strings::split("|", $filter_chunks[1]) : Strings::emptyArray();

            $include_flag = false;
            for ($n2 = 0; $n2 < SIZE($include_chunks); $n2++) {
                $include_chunk = Regex::escape($include_chunks[$n2]);
                if (!BLANK($this->description) && Regex::isMatch($this->description, $include_chunk, RegexOptions::IgnoreCase))
                    $include_flag |= true;
                if (Regex::isMatch($this->title, $include_chunk, RegexOptions::IgnoreCase))
                    $include_flag |= true;
            }
            for ($n3 = 0; $n3 < SIZE($exclude_chunks); $n3++) {
                $exclude_chunk = Regex::escape($exclude_chunks[$n3]);
                if (!BLANK($this->description) && Regex::isMatch($this->description, $exclude_chunk, RegexOptions::IgnoreCase))
                    $include_flag &= false;
                if (Regex::isMatch($this->title, $exclude_chunk, RegexOptions::IgnoreCase))
                    $include_flag |= true;
            }
            if ($include_flag) {
                $category_tags = /*(TString[])*/ADD($category_tags, $name);
            }
        }
        if (SIZE($category_tags) == 0)
            return;

        //TODO
        //$unique_categories = $this->NormalizeList($category_tags, $lang);
        //$category = TString::join(", ", $unique_categories);

        $this->category = Strings::join(", ", $category_tags);
    }

    /**
     * Process creator (publisher, company etc).
     */
    public function processCreator() {
        // Extract creator from item (if it is not set yet)
        if ($this->creator == null) {
            if (!BLANK($this->item->get("company")))
                $this->creator = $this->item->get("company");
            else if (!BLANK($this->item->get("source")))
                $this->creator = $this->item->get("source");
            else if (!BLANK($this->item->get("dc"))) { //TODO implement [dc][creator]
                $temp = /*(Hashtable)*/$this->item->get("dc");
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
    public function getUrlTitle($translit = false) {
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
