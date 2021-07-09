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

use Bula\Objects\Regex;
use Bula\Objects\RegexOptions;

use Bula\Objects\Arrays;
use Bula\Objects\DateTimes;
use Bula\Objects\Strings;
use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
use Bula\Objects\TString;

use Bula\Model\DataSet;

require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");

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
    /** Final (processed) date */
    public $date = null;

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
     * @param THashtable $source Current processed source.
     * @param THashtable $item Current processed RSS-item from given source.
     */
    private function initialize($source, THashtable $item)
    {
        $this->source = $source;
        $this->item = $item;

        $this->link = ($item->get("link"))->trim();

        // Pre-process full description & title
        // Trick to eliminate non-UTF-8 characters
        $this->fullTitle = Strings::cleanChars($item->get("title"));

        if ($item->containsKey("description") && !BLANK($item->get("description"))) {
            $this->fullDescription = Strings::cleanChars($item->get("description"));
            $this->fullDescription = Strings::replace("\n", "\r\n", $this->fullDescription);
            $this->fullDescription = Strings::replace("\r\r", "\r", $this->fullDescription);
        }

        $this->preProcessLink();
    }

    /**
     * Pre-process link (just placeholder for now)
     */
    protected function preProcessLink()
    {}

    public function processMappings($dsMappings)
    {
        $title = $this->fullTitle;
        $description = $this->fullDescription;

        for ($n = 0; $n < $dsMappings->getSize(); $n++) {
            $oMapping = $dsMappings->getRow($n);
            $from = $oMapping->get("s_From");
            $to = $oMapping->get("s_To");
            $title = Strings::replace($from, $to, $title);
            if ($description != null)
                $description = Strings::replace($from, $to, $description);
        }

        $this->title = $title;
        if ($description != null)
            $this->description = $description;
    }

    /**
     * Process description.
     */
    public function processDescription()
    {
        $BR = "\n";

        $title = Strings::removeTags($this->title);
        // Normalize \r\n to \n
        $title = Regex::replace($title, "\r\n", $BR);
        $title = Regex::replace($title, "(^&)#", "$1[sharp]");
        $title = $title->trim(); //TODO -- sometimes doesn't work...

        $title = Regex::replace($title, "[\n]+", $BR);
        $title = Regex::replace($title, $BR, EOL);

        $this->title = $title;

        if ($this->description == null)
            return;
        // Normalize \r\n to \n
        $description = Regex::replace($this->description, "\r\n", $BR);

        //TODO -- Fixes for FetchRSS feeds (parsed from Twitter) here...
        $description = $description->replace("&#160;", " ");
        $description = $description->replace("&nbsp;", " ");

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
        $description = Regex::replace($description, "[ \t]+\n", "\n");
        $description = Regex::replace($description, "[\n]+\n\n", "\n\n");
        $description = Regex::replace($description, "\n\n[ \t]*[\\+\\-\\*][^\\+\\-\\*][ \t]*", "\n* ");
        $description = Regex::replace($description, "[ \t]+", " ");

        $description = $description->trim();

        // Normalize back to \r\n
        $this->description = Regex::replace($description, $BR, EOL);
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

        if ($categoryItem->isEmpty())
            return null;

        $category = null;
        $categoriesArr = Strings::split(",", $categoryItem);
        $categoriesNew = new TArrayList();
        for ($c = 0; $c < SIZE($categoriesArr); $c++) {
            $temp = $categoriesArr[$c];
                $temp = Strings::trim($temp);
            if (BLANK($temp))
                continue;
            $temp = Strings::firstCharToUpper($temp);
            if ($category == null)
                $category = $temp;
            else
                $category = $category->concat(CAT(", ", $temp));
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
     * @return Integer Number of added categories.
     */
    public function addStandardCategories($dsCategories, $lang)
    {
        //if (BLANK($this->description))
        //    return;

        $categoryTags = new TArrayList();
        if (!BLANK($this->category))
            $categoryTags->addAll(Strings::split(", ", $this->category));
        for ($n1 = 0; $n1 < $dsCategories->getSize(); $n1++) {
            $oCategory = $dsCategories->getRow($n1);
            $rssAllowedKey = $oCategory->get("s_CatId");
            $name = $oCategory->get("s_Name");

            $filterValue = $oCategory->get("s_Filter");
            $filterChunks = Strings::split("~", $filterValue);
            $includeChunks = SIZE($filterChunks) > 0 ?
                Strings::split("\\|", $filterChunks[0]) : Strings::emptyArray();
            $excludeChunks = SIZE($filterChunks) > 1 ?
                Strings::split("\\|", $filterChunks[1]) : Strings::emptyArray();

            $includeFlag = false;
            for ($n2 = 0; $n2 < SIZE($includeChunks); $n2++) {
                $includeChunk = $includeChunks[$n2]; //Regex::escape($includeChunks[$n2]);
                if (Regex::isMatch($this->title, $includeChunk, RegexOptions::IgnoreCase)) {
                    $includeFlag |= true;
                    break;
                }
                if (!BLANK($this->description) && Regex::isMatch($this->description, $includeChunk, RegexOptions::IgnoreCase)) {
                    $includeFlag |= true;
                    break;
                }
            }
            for ($n3 = 0; $n3 < SIZE($excludeChunks); $n3++) {
                $excludeChunk = $excludeChunks[$n3]; //Regex::escape($excludeChunks[$n3]);
                if (Regex::isMatch($this->title, $excludeChunk, RegexOptions::IgnoreCase)) {
                    $includeFlag &= false;
                    break;
                }
                if (!BLANK($this->description) && Regex::isMatch($this->description, $excludeChunk, RegexOptions::IgnoreCase)) {
                    $includeFlag &= false;
                    break;
                }
           }
            if ($includeFlag)
                $categoryTags->add($name);
        }
        if ($categoryTags->size() == 0)
            return 0;

        //TODO
        //$uniqueCategories = $this->NormalizeList($categoryTags, $lang);
        //$category = TString::join(", ", $uniqueCategories);

        $this->category = Strings::join(", ", $categoryTags->toArray(
        ));

        return $categoryTags->size();
    }

    /**
     * Normalize list of categories.
     */
    public function normalizeCategories()
    {
        if (BLANK($this->category))
            return;

        $categories = Strings::split(", ", $this->category);
        $size = SIZE($categories);
        if ($size == 1)
            return;

        $categoryTags = new TArrayList();
        for ($n1 = 0; $n1 < $size; $n1++) {
            $category1 = $categories[$n1];
            if (!$categoryTags->contains($category1))
                $categoryTags->add($category1);
        }

        $this->category = Strings::join(", ", $categoryTags->toArray(
        ));
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
     * Process rules.
     * @param DataSet $rules The list of rules to process.
     * @return Integer Number of rules applied.
     */
    public function processRules($rules)
    {
        $counter = 0;
        for ($n = 0; $n < $rules->getSize(); $n++) {
            $rule = $rules->getRow($n);
            $sourceName = $rule->get("s_SourceName");
            if (EQ($sourceName, "*") || EQ($sourceName, $this->source))
                $counter += $this->processRule($sourceName, $rule);
        }
        return $counter;
    }

    private function processRule($sourceName, $rule)
    {
        $counter = 0;
        $nameTo = $rule->get("s_To");
        $valueTo = null;
        $nameFrom = NUL($rule->get("s_From")) ? $nameTo : $rule->get("s_From");
        $valueFrom = $this->getString($nameFrom);
        $operation = $rule->get("s_Operation");
        $intValue = INT($rule->get("i_Value"));
        $pattern = $rule->get("s_Pattern");
        $stringValue = $rule->get("s_Value");
        $append = false;
        if (EQ($operation, "get") && !NUL($valueFrom)) {
            $valueTo = $valueFrom;
        }
        else if (EQ($operation, "shrink") && !NUL($valueFrom) && LEN($pattern) > 0) {
            $shrinkIndex = $valueFrom->indexOf($pattern);
            if ($shrinkIndex != -1)
                $valueTo = $valueFrom->substring(0, $shrinkIndex)->trim();
        }
        else if (EQ($operation, "cut") && !NUL($valueFrom) && LEN($pattern) > 0) {
            $cutIndex = $valueFrom->indexOf($pattern);
            if ($cutIndex != -1)
                $valueTo = $valueFrom->substring($cutIndex + LEN($pattern));
        }
        else if (EQ($operation, "replace") && !NUL($valueFrom) && LEN($pattern) > 0) {
            $valueTo = Regex::replace($valueFrom, $pattern, $stringValue, RegexOptions::IgnoreCase);
        }
        else if (EQ($operation, "remove") && !NUL($valueFrom) && LEN($pattern) > 0) {
            $matches =
                Regex::matches($valueFrom, $pattern, RegexOptions::IgnoreCase);
            if (SIZE($matches) > 0)
                $valueTo = $valueFrom->replace($matches[0], "");
        }
        else if (EQ($operation, "truncate") && !NUL($valueFrom) && $intValue > 0) {
            if (LEN($valueFrom) > $intValue) {
                $valueTo = $valueFrom->substring(0, $intValue);
                while (!$valueTo->endsWith(" "))
                    $valueTo = $valueTo->substring(0, LEN($valueTo) - 1);
                $valueTo = $valueTo->concat("...");
            }
        }
        else if (EQ($operation, "extract") && !NUL($valueFrom)) {
            $groups =
                Regex::matches($valueFrom, $pattern, RegexOptions::IgnoreCase);
            if (SIZE($groups) > $intValue) {
                if (BLANK($stringValue))
                    $valueTo = $groups[$intValue];
                else {
                    if (EQ($nameTo, "date")) {
                        $valueTo = DateTimes::format(DateTimes::RSS_DTS, DateTimes::parse($stringValue, $groups[$intValue]));
                    }
                    else {
                        $valueTo = $stringValue;
                        for ($n = 0; $n < SIZE($groups); $n++) {
                            if ($valueTo->indexOf(CAT("$", $n)) != -1)
                                $valueTo = $valueTo->replace(CAT("$", $n), $groups[$n]);
                        }
                    }
                }
                if (EQ($nameTo, "category"))
                    $append = true;
            }
        }
        else if (EQ($operation, "map")) {
            //TODO
        }
        if (!NUL($valueTo))
            $this->setString($nameTo, $valueTo, $append);
        return $counter;
    }

    private function setString($name, $value, $append)
    {
        if (EQ($name, "link"))
            $this->link = $value;
        else if (EQ($name, "title"))
            $this->title = $value;
        else if (EQ($name, "description"))
            $this->description = $value;
        else if (EQ($name, "date"))
            $this->date = $value;
        else if (EQ($name, "category")) {
            if (BLANK($this->category) || !$append)
                $this->category = $value;
            else if ($append)
                $this->category = new TString(CAT($value, ", ", $this->category));
        }
        else if (EQ($name, "creator"))
            $this->creator = $value;
        else if (EQ($name, "custom1"))
            $this->custom1 = $value;
        else if (EQ($name, "custom2"))
            $this->custom2 = $value;
    }

    private function getString($name)
    {
        if (EQ($name, "link"))
            return $this->link;
        else if (EQ($name, "title"))
            return $this->title;
        else if (EQ($name, "description"))
            return $this->description;
        else if (EQ($name, "date"))
            return $this->date;
        else if (EQ($name, "category"))
            return $this->category;
        else if (EQ($name, "creator"))
            return $this->creator;
        else if (EQ($name, "custom1"))
            return $this->custom1;
        else if (EQ($name, "custom2"))
            return $this->custom2;
        else if ($this->item->containsKey($name))
            return $this->item->get($name);
        return null;
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

        $title = Regex::replace($title, "\\&amp\\;", " and ");
        $title = Regex::replace($title, "[^A-Za-z0-9]", "-");
        $title = Regex::replace($title, "[\\-]+", "-");
        $title = $title->trim("-")->toLowerCase();
        return $title;
    }
}
