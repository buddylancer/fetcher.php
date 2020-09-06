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
use Bula\Objects\TString;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");

/**
 * Logic for generating Menu block.
 */
class Menu extends Page {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public Menu(Context context) : base(context) { }
    CS*/

    /** Execute main logic for Menu block */
    public function execute() {
        $public_pages = new ArrayList();

        $public_pages->add("Home");
        $public_pages->add("home");
        if ($this->context->IsMobile) {
            $public_pages->add(Config::NAME_ITEMS); $public_pages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Categories")) {
                $public_pages->add(CAT("By ", $this->context->get("Name_Categories")));
                $public_pages->add("#items_by_skills");
                //$public_pages->add("RSS Feeds");
                //$public_pages->add("#read_rss_feeds");
            }
            $public_pages->add("Sources");
            $public_pages->add("sources");
        }
        else {
            $public_pages->add(CAT("Browse ", Config::NAME_ITEMS));
            $public_pages->add("items");
            if (Config::SHOW_BOTTOM && $this->context->contains("Name_Categories")) {
                $public_pages->add(CAT(Config::NAME_ITEMS, " by ", $this->context->get("Name_Categories")));
                $public_pages->add("#items_by_skills");

                $public_pages->add("Read RSS Feeds");
                $public_pages->add("#read_rss_feeds");
            }
            $public_pages->add("Sources");
            $public_pages->add("sources");
        }

        $MenuItems = new ArrayList();
        for ($n = 0; $n < $public_pages->count(); $n += 2) {
            $Row = new Hashtable();
            $title = $public_pages->get($n+0);
            $page = $public_pages->get($n+1);
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
            $Row->put("[#Link]", $href);
            $Row->put("[#LinkText]", $title);
            $Row->put("[#Prefix]", $n != 0 ? " &bull; " : " ");
            $MenuItems->add($Row);
        }

        $Prepare = new Hashtable();
        $Prepare->put("[#MenuItems]", $MenuItems);
        $this->write("Bula/Fetcher/View/menu.html", $Prepare);
    }
}

