<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher;

/**
 * Main class for configuring data.
 */
class Config
{
    /** Platform */
    const PLATFORM = "PHP";
    /** Exactly the same as RewriteBase in .htaccess */
    const TOP_DIR = "/";
    /** Index page name */
    const INDEX_PAGE = "";
    /** Action page name */
    const ACTION_PAGE = "action[#File_Ext]";
    /** RSS-feeds page name */
    const RSS_PAGE = "rss[#File_Ext]";
    /** Current API output format (can be "Json" or "Xml" for now) */
    const API_FORMAT = "Json";
    /** Current API output content type (can be "application/json" or "text/xml" for now) */
    const API_CONTENT = "application/json";
    /** File prefix for constructing real path */
    const FILE_PREFIX = "";

    /** Security code */
    const SECURITY_CODE = "1234";

    /** Use fine or full URLs */
    const FINE_URLS = false;

    /** Cache Web-pages */
    const CACHE_PAGES = false;
    /** Cache RSS-feeds */
    const CACHE_RSS = false;
    /** Show what source an item is originally from */
    const SHOW_FROM = false;
    /** Whether to show images for sources */
    const SHOW_IMAGES = false;
    /** File extension for images */
    const EXT_IMAGES = "gif";
    /** Show an item or immediately redirect to external source item */
    const IMMEDIATE_REDIRECT = false;
    /** How much items to show on "Sources" page */
    const LATEST_ITEMS = 3;
    /** Minimum number of items in RSS-feeds */
    const MIN_RSS_ITEMS = 5;
    /** Maximum number of items in RSS-feeds */
    const MAX_RSS_ITEMS = 50;

    /** Default number of rows on page */
    const DB_ROWS = 20;
    /** Default number of rows on "Home" page */
    const DB_HOME_ROWS = 15;
    /** Default number of rows on "Items" page */
    const DB_ITEMS_ROWS = 25;

    // Fill these fields by your site data
    /** Site language (default - null) */
    const SITE_LANGUAGE = null;
    /** Site name */
    const SITE_NAME = "Buddy Fetcher";
    /** Site comments */
    const SITE_COMMENTS = "Latest News Headlines";
    /** Site keywords */
    const SITE_KEYWORDS = "Buddy Fetcher, rss, fetcher, aggregator, [#Platform], MySQL";
    /** Site description */
    const SITE_DESCRIPTION = "Buddy Fetcher is a simple RSS fetcher/aggregator written in [#Platform]/MySQL";

    /** Name of item (in singular form) */
    const NAME_ITEM = "Headline";
    /** Name of items (in plural form) */
    const NAME_ITEMS = "Headlines";
    // Uncomment what fields should be extracted and name them appropriately
    /** Name of category (in singular form) */
    const NAME_CATEGORY = "Region";
    /** Name of categories (in plural form) */
    const NAME_CATEGORIES = "Regions";
    /** Name of creator */
    const NAME_CREATOR = "Creator";
    /** Name of custom field 1 (comment when not extracted) */
    //const NAME_CUSTOM1 = "Custom1";
    /** Name of custom field 2 (comment when not extracted) */
    //const NAME_CUSTOM2 = "Custom2";

    /** Show bottom blocks (Filtering and RSS) */
    const SHOW_BOTTOM = true;
    /** Show empty categories */
    const SHOW_EMPTY = false;
    /** Sort categories by Id (s_CatId) or Name (s_Name) or NULL for default (as-is) */
    const SORT_CATEGORIES = null;

    /** Site time shift with respect to GMT (hours*100+minutes) */
    const TIME_SHIFT = 0;
    /** Site time zone name (GMT or any other) */
    const TIME_ZONE = "GMT";

    /** Powered By string */
    const POWERED_BY = "Buddy Fetcher for [#Platform]";
    /** GitHub repository */
    const GITHUB_REPO = "buddylancer/fetcher.[#Platform]";
}
