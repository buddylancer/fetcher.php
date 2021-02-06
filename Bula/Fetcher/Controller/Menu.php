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
use Bula\Objects\TString;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");

/**
 * Logic for generating Menu block.
 */
class Menu extends Page
{

    /** Execute main logic for Menu block */
    public function execute()
    {
        $publicPages = new ArrayList();

        $publicPages->add("Home");
        $publicPages->add("home");
        if ($this->context->IsMobile) {
            $publicPages->add(Config::NAME_ITEMS); $publicPages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Categories")) {
                $publicPages->add(CAT("By ", $this->context->get("Name_Categories")));
                $publicPages->add("#items_by_skills");
                //$publicPages->add("RSS Feeds");
                //$publicPages->add("#read_rss_feeds");
            }
            $publicPages->add("Sources");
            $publicPages->add("sources");
        }
        else {
            $publicPages->add(CAT("Browse ", Config::NAME_ITEMS));
            $publicPages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Categories")) {
                $publicPages->add(CAT(Config::NAME_ITEMS, " by ", $this->context->get("Name_Categories")));
                $publicPages->add("#items_by_skills");

                $publicPages->add("Read RSS Feeds");
                $publicPages->add("#read_rss_feeds");
            }
            $publicPages->add("Sources");
            $publicPages->add("sources");
        }

        $menuItems = new ArrayList();
        for ($n = 0; $n < $publicPages->count(); $n += 2) {
            $row = new Hashtable();
            $title = $publicPages->get($n+0);
            $page = $publicPages->get($n+1);
            $href = null;
            if (EQ($page, "home"))
                $href = Config::TOP_DIR;
            else {
                if (EQ($page->substring(0, 1), "#"))
                    $href = $page;
                else {
                    $href = CAT(Config::TOP_DIR, Config::INDEX_PAGE, "?p=", $page);
                    if ($this->context->FineUrls)
                        $href = CAT(Config::TOP_DIR, $page);
                }
            }
            $row->put("[#Link]", $href);
            $row->put("[#LinkText]", $title);
            $row->put("[#Prefix]", $n != 0 ? " &bull; " : " ");
            $menuItems->add($row);
        }

        $prepare = new Hashtable();
        $prepare->put("[#MenuItems]", $menuItems);
        $this->write("menu", $prepare);
    }
}

