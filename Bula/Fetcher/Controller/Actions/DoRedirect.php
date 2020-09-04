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

use Bula\Fetcher\Config;

use Bula\Objects\Response;
use Bula\Objects\Hashtable;

use Bula\Fetcher\Controller\Page;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Fetcher/Controller/Engine.php");

/**
 * Base class for redirecting from the web-site.
 */
abstract class DoRedirect extends Page {

    /**
     * Execute main logic for this action.
     * @param TString $link_to_redirect Link to redirect (or null if there were some errors).
     * @param TString $error_message Error to show (or null if no errors).
     */
    public function executeRedirect($link_to_redirect, $error_message) {
        $Prepare = new Hashtable();
        $template_name = null;
        if (!NUL($error_message)) {
            $Prepare->put("[#Title]", "Error");
            $Prepare->put("[#ErrMessage]", $error_message);
            $template_name = "Bula/Fetcher/View/error_alone.html";
        }
        else if (!BLANK($link_to_redirect)) {
            $Prepare->put("[#Link]", $link_to_redirect);
            $template_name = "Bula/Fetcher/View/redirect.html";
        }

        $engine = $this->context->pushEngine(true);
        Response::write($engine->showTemplate($template_name, $Prepare)->getValue());
    }
}
