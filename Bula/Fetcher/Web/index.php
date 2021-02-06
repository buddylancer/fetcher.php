<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Web;

// Very straight-forward *initial* setting of include path.
set_include_path("../../../");

include("../Context.php");
$context = new \Bula\Fetcher\Context();

require_once("Bula/Fetcher/Controller/Index.php");
use Bula\Fetcher\Controller\Index;

error_reporting(E_ALL);
date_default_timezone_set("UTC");
$index = new Index($context);
$index->execute();
