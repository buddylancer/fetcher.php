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
use Bula\Objects\TString;
use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");

/**
 * Logic for generating Menu block.
 */
class Menu extends Page
{

    /** Execute main logic for Menu block */
    public function execute()
    {
        $publicPages = new TArrayList();

        $bookmark = null;
        if ($this->context->contains("Name_Category"))
            $bookmark = CAT("#", Config::NAME_ITEMS, "_by_", $this->context->get("Name_Category"));
        $publicPages->add("Home");
        $publicPages->add("home");
        if ($this->context->IsMobile) {
            $publicPages->add(Config::NAME_ITEMS); $publicPages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Category")) {
                $publicPages->add(CAT("By ", $this->context->get("Name_Category")));
                $publicPages->add($bookmark);
                //$publicPages->add("RSS Feeds");
                //$publicPages->add("#read_rss_feeds");
            }
            $publicPages->add("Sources");
            $publicPages->add("sources");
        }
        else {
            $publicPages->add(CAT("Browse ", Config::NAME_ITEMS));
            $publicPages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Category")) {
                $publicPages->add(CAT(Config::NAME_ITEMS, " by ", $this->context->get("Name_Category")));
                $publicPages->add($bookmark);

                $publicPages->add("Read RSS Feeds");
                $publicPages->add("#Read_RSS_Feeds");
            }
            $publicPages->add("Sources");
            $publicPages->add("sources");
        }

        $menuItems = new TArrayList();
        for ($n = 0; $n < $publicPages->size(); $n += 2) {
            $row = new THashtable();
            $title = $publicPages->get($n+0);
            $page = $publicPages->get($n+1);
            $href = null;
            if (EQ($page, "home"))
                $href = Config::TOP_DIR;
            else {
                if (EQ($page->substring(0, 1), "#"))
                    $href = $page;
                else {
                    $href = $this->getLink(Config::INDEX_PAGE, "?p=", null, $page);
                }
            }
            $row->put("[#Link]", $href);
            $row->put("[#LinkText]", $title);
            $row->put("[#Prefix]", $n != 0 ? " &bull; " : " ");
            $menuItems->add($row);
        }

        $prepare = new THashtable();
        $prepare->put("[#MenuItems]", $menuItems);
        $this->write("menu", $prepare);
    }
}

