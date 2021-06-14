<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Actions;

use Bula\Objects\DateTimes;
use Bula\Objects\Enumerator;
use Bula\Objects\Helper;
use Bula\Objects\Logger;
use Bula\Objects\Request;
use Bula\Objects\Strings;
use Bula\Objects\TString;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Fetcher\Controller\Page;
use Bula\Fetcher\Controller\Util;

/**
 * Action for cleaning cache.
 */
class DoCleanCache extends Page
{

    /** Execute main logic for DoCleanCache action */
    public function execute()
    {
        $oLogger = new Logger();
        $log = $this->context->Request->getOptionalInteger("log");
        if (!NUL($log) && $log != -99999) {
            $filenameTemplate = new TString("C:/Temp/Log_{0}_{1}.html");
            $filename = Util::formatString($filenameTemplate, ARR("do_clean_cache", DateTimes::format(DateTimes::SQL_DTS)));
            $oLogger->initFile($filename);
        }
        else
            $oLogger->initResponse($this->context->Response);
        $this->cleanCache($oLogger);
    }

    /**
     * Actual cleaning of cache folder.
     * @param Logger $oLogger Logger instance.
     * @param TString $pathName Cache folder name (path).
     * @param TString $ext Files extension to clean.
     */
    private function cleanCacheFolder($oLogger, $pathName, $ext)
    {
        if (!Helper::dirExists($pathName))
            return;

        $entries = Helper::listDirEntries($pathName);
        while ($entries->moveNext()) {
            $entry = new TString($entries->current());

            if (Helper::isFile($entry) && $entry->endsWith($ext)) {
                $oLogger->output(CAT("Deleting of ", $entry, " ...<br/>", EOL));
                Helper::deleteFile($entry);
            }
            else if (Helper::isDir($entry)) {
                $oLogger->output(CAT("Drilling to ", $entry, " ...<br/>", EOL));
                self::cleanCacheFolder($oLogger, $entry, $ext);
            }
            //unlink($pathName); //Comment for now -- dangerous operation!!!
        }
    }

    /**
     * Clean all cached info (both for Web and RSS).
     */
    public function cleanCache($oLogger)
    {
        // Clean cached rss content
        $oLogger->output(CAT("Cleaning Rss Folder ", $this->context->RssFolderRoot, " ...<br/>", EOL));
        $rssFolder = Strings::concat($this->context->RssFolderRoot);
        $this->cleanCacheFolder($oLogger, $rssFolder, ".xml");

        // Clean cached pages content
        $oLogger->output(CAT("Cleaning Cache Folder ", $this->context->CacheFolderRoot,  "...<br/>", EOL));
        $cacheFolder = Strings::concat($this->context->CacheFolderRoot);
        $this->cleanCacheFolder($oLogger, $cacheFolder, ".cache");

        $oLogger->output(CAT("<br/>... Done.<br/>", EOL));
    }

}
