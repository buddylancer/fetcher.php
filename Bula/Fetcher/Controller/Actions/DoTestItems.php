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

use Bula\Objects\Response;
use Bula\Objects\DateTimes;
use Bula\Objects\DataRange;
use Bula\Model\DataSet;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Fetcher\Model\DOTime;
use Bula\Fetcher\Controller\Page;
use Bula\Fetcher\Controller\BOFetcher;

require_once("Bula/Fetcher/Model/DOTime.php");
require_once("Bula/Fetcher/Controller/BOFetcher.php");

/**
 * Testing sources for necessary fetching.
 */
class DoTestItems extends Page
{

    private static $TOP = null;
    private static $BOTTOM = null;

    /** Initialize TOP and BOTTOM blocks. */
    public static function initialize()
    {
        self::$TOP = CAT(
            "<!DOCTYPE html>", EOL,
            "<html xmlns=\"http://www.w3.org/1999/xhtml\">", EOL,
            "    <head>", EOL,
            "        <title>Buddy Fetcher -- Test for new items</title>", EOL,
            "        <meta name=\"keywords\" content=\"Buddy Fetcher, rss, fetcher, aggregator, PHP, MySQL\" />", EOL,
            "        <meta name=\"description\" content=\"Buddy Fetcher is a simple RSS Fetcher/aggregator written in PHP/MySQL\" />", EOL,
            "        <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />", EOL,
            "    </head>", EOL,
            "    <body>", EOL
        );
        self::$BOTTOM = CAT(
            "    </body>", EOL,
            "</html>", EOL
        );
    }

    /** Execute main logic for DoTestItems action */
    public function execute()
    {
        $insertRequired = false;
        $updateRequired = false;

        $doTime = new DOTime();

        $dsTimes = $doTime->getById(1);
        $timeShift = 240; // 4 min
        $currentTime = DateTimes::getTime();
        if ($dsTimes->getSize() > 0) {
            $oTime = $dsTimes->getRow(0);
            if ($currentTime > DateTimes::getTime($oTime->get("d_Time")) + $timeShift)
                $updateRequired = true;
        }
        else
            $insertRequired = true;

        $this->context->Response->write(self::$TOP);
        if ($updateRequired || $insertRequired) {
            $this->context->Response->write(CAT("Fetching new items... Please wait...<br/>", EOL));

            $boFetcher = new BOFetcher($this->context);
            $boFetcher->fetchFromSources();

            $doTime = new DOTime(); // Need for DB reopen
            $fields = new DataRange();
            $fields->put("d_Time", DateTimes::format(DateTimes::SQL_DTS, DateTimes::getTime()));
            if ($insertRequired) {
                $fields->put("i_Id", 1);
                $doTime->insert($fields);
            }
            else
                $doTime->updateById(1, $fields);
        }
        else
            $this->context->Response->write(CAT("<hr/>Fetch is not required<br/>", EOL));
        $this->context->Response->write(self::$BOTTOM);
    }
}
DoTestItems::initialize();
