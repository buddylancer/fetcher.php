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
    /** Exactly the same as RewriteBase in .htaccess */
    const TOP_DIR = "/";
    /** Index page name */
    const INDEX_PAGE = "";
    /** Action page name */
    const ACTION_PAGE = "action.php";
    /** RSS-feeds page name */
    const RSS_PAGE = "rss.php";
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
    /** Site name */
    const SITE_NAME = "Buddy Fetcher";
    /** Site comments */
    const SITE_COMMENTS = "Latest Items";
    /** Site keywords */
    const SITE_KEYWORDS = "Buddy Fetcher, rss, fetcher, aggregator, PHP, MySQL";
    /** Site description */
    const SITE_DESCRIPTION = "Buddy Fetcher is a simple RSS fetcher/aggregator written in PHP/MySQL";

    /** Name of item (in singular form) */
    const NAME_ITEM = "Item";
    /** Name of items (in plural form) */
    const NAME_ITEMS = "Items";
    // Uncomment what fields should be extracted and name them appropriately
    /** Name of category (in singular form) */
    const NAME_CATEGORY = "Category";
    /** Name of categories (in plural form) */
    const NAME_CATEGORIES = "Categories";
    /** Name of creator */
    const NAME_CREATOR = "Creator";
    /** Name of custom field 1 (comment when not extracted) */
    //const NAME_CUSTOM1 = "Custom1";
    /** Name of custom field 2 (comment when not extracted) */
    //const NAME_CUSTOM2 = "Custom2";

    /** Show bottom blocks (Filtering and RSS) */
    const SHOW_BOTTOM = true;

    /** Powered By string */
    const POWERED_BY = "Buddy Fetcher";
    /** GitHub repository */
    const GITHUB_REPO = "buddylancer/fetcher.php";
}
