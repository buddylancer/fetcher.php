<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\Response;
use Bula\Objects\DateTimes;
use Bula\Objects\Helper;
use Bula\Objects\TString;

/**
 * Simple logger.
 */
class Logger
{
    private $fileName = null;

    /**
     * Initialize logging into file.
     * @param TString $filename Log file name.
     */
    public function init($filename)
    {
        $this->fileName = $filename;
        if (!$filename->isEmpty()) {
            if (Helper::fileExists($filename))
                Helper::deleteFile($filename);
        }
    }

    /**
     * Log text string.
     * @param TString $text Content to log.
     */
    public function output($text)
    {
        if ($this->fileName == null) {
            if ($text instanceof TString) $buffer = $buffer->getValue();
            Response::write($text);
            return;
        }
        if (Helper::fileExists($this->fileName))
            Helper::appendText($this->fileName, $text);
        else {
            Helper::testFileFolder($this->fileName);
            Helper::writeText($this->fileName, $text);
        }

    }

    /**
     * Log text string + current time.
     * @param TString $text Content to log.
     */
    public function time($text)
    {
        $this->output(CAT($text, " -- ", DateTimes::format("H:i:s"), "<br/>", EOL));
    }
}
