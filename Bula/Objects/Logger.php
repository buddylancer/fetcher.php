<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
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
class Logger {
    private $file_name = null;

    /**
     * Initialize logging into file.
     * @param TString $filename Log file name.
     */
    public function init($filename) {
        $this->file_name = $filename;
        if (!$filename->isEmpty()) {
            if (Helper::fileExists($filename))
                Helper::deleteFile($filename);
        }
    }

    /**
     * Log text string.
     * @param TString $text Content to log.
     */
    public function output($text) {
        if ($this->file_name == null) {
            if ($text instanceof TString) $buffer = $buffer->getValue();
            Response::write($text);
            return;
        }
        if (Helper::fileExists($this->file_name))
            Helper::appendText($this->file_name, $text);
        else
            Helper::writeText($this->file_name, $text);

    }

    /**
     * Log text string + current time.
     * @param TString $text Content to log.
     */
    public function time($text) {
        $this->output(CAT($text, " -- ", DateTimes::format("H:i:s"), "<br/>\r\n"));
    }
}
