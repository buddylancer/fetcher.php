<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Actions;

use Bula\Objects\Helper;
use Bula\Objects\Logger;
use Bula\Objects\Strings;
use Bula\Objects\TString;
use Bula\Fetcher\Controller\Page;

/**
 * Action for cleaning cache.
 */
class DoCleanCache extends Page {
    /**
     * Public default constructor.
     * @param Context $context Context instance.
     * /
    public DoCleanCache(Context context) : base(context) { }
    CS*/

    /** Execute main logic for DoCleanCache action */
    public function execute() {
        $oLogger = new Logger();
        $log = Request::getOptionalInteger("log");
        if (!NUL($log) && $log != -99999) {
            $filename_template = new TString("C:/Temp/Log_{0}_{1}.html");
            $filename = Util::formatString($filename_template, ARR("do_clean_cache", DateTimes::format(Config::SQL_DTS)));
            $oLogger->init($filename);
        }
        $this->cleanCache($oLogger);
    }

    /**
     * Actual cleaning of cache folder.
     * @param Logger $oLogger Logger instance.
     * @param TString $path_name Cache folder name (path).
     * @param TString $ext Files extension to clean.
     */
    private function cleanCacheFolder($oLogger, $path_name, $ext) {
        if (!Helper::dirExists($path_name))
            return;

        $entries = Helper::listDirEntries($path_name);
        while ($entries->moveNext()) {
            $entry = new TString($entries->current());

            if (Helper::isFile($entry) && $entry->endsWith($ext)) {
                $oLogger->output(CAT("Deleting of ", $entry, " ...<br/>\r\n"));
                Helper::deleteFile($entry);
            }
            else if (Helper::isDir($entry)) {
                $oLogger->output(CAT("Drilling to ", $entry, " ...<br/>\r\n"));
                self::cleanCacheFolder($oLogger, $entry, $ext);
            }
            //unlink($path_name); //Comment for now -- dangerous operation!!!
        }
    }

    /**
     * Clean all cached info (both for Web and RSS).
     */
    public function cleanCache($oLogger) {
        // Clean cached rss content
        $oLogger->output(CAT("Cleaning Rss Folder ", $this->context->RssFolderRoot, " ...<br/>\r\n"));
        $rssFolder = Strings::concat($this->context->RssFolderRoot);
        $this->cleanCacheFolder($oLogger, $rssFolder, ".xml");

        // Clean cached pages content
        $oLogger->output(CAT("Cleaning Cache Folder ", $this->context->CacheFolderRoot,  "...<br/>\r\n"));
        $cacheFolder = Strings::concat($this->context->CacheFolderRoot);
        $this->cleanCacheFolder($oLogger, $cacheFolder, ".cache");

        $oLogger->output("<br/>... Done.<br/>\r\n");
    }

}
