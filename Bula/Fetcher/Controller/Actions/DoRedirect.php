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

use Bula\Fetcher\Config;

use Bula\Objects\Response;
use Bula\Objects\Hashtable;

use Bula\Fetcher\Controller\Page;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Controller/Engine.php");

/**
 * Base class for redirecting from the web-site.
 */
abstract class DoRedirect extends Page
{

    /**
     * Execute main logic for this action.
     * @param TString $linkToRedirect Link to redirect (or null if there were some errors).
     * @param TString $errorMessage Error to show (or null if no errors).
     */
    public function executeRedirect($linkToRedirect, $errorMessage)
    {
        $prepare = new Hashtable();
        $templateName = null;
        if (!NUL($errorMessage)) {
            $prepare->put("[#Title]", "Error");
            $prepare->put("[#ErrMessage]", $errorMessage);
            $templateName = "Bula/Fetcher/View/error_alone.html";
        }
        else if (!BLANK($linkToRedirect)) {
            $prepare->put("[#Link]", $linkToRedirect);
            $templateName = "Bula/Fetcher/View/redirect.html";
        }

        $engine = $this->context->pushEngine(true);
        Response::write($engine->showTemplate($templateName, $prepare)->getValue());
    }
}
