<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Internal;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;

use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;

use Bula\Objects\Arrays;
use Bula\Objects\Helper;
use Bula\Objects\Strings;
use Bula\Objects\TString;
use Bula\Objects\TResponse;

require_once("Bula/Internal.php");
require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/TString.php");

/**
 * Engine for processing templates.
 */
class Engine
{
    /** Instance of Context */
    public $context = null;
    private $printFlag = false;
    private $printString = "";

    /** Public default constructor */
    public function __construct($context)
    {
        $this->context = $context;
        $this->printFlag = false;
        $this->printString = "";
    }

    /**
     * Set print string for current engine instance.
     * @param TString $val Print string to set.
     */
    public function setPrintString($val)
    {
        $this->printString = $val;
    }

    /**
     * Get print string for current engine instance.
     * @return TString Current print string.
     */
    public function getPrintString()
    {
        return $this->printString;
    }

    /**
     * Set print flag for current engine instance.
     * @param Boolean $val Print flag to set.
     */
    public function setPrintFlag($val)
    {
        $this->printFlag = $val;
    }

    /**
     * Get print flag for current engine instance.
     * @return Boolean Current print flag.
     */
    public function getPrintFlag()
    {
        return $this->printFlag;
    }

    /**
     * Write string.
     * @param TString $val String to write.
     */
    public function write($val)
    {
        if ($this->printFlag) {
            $langFile = null;
            if (BLANK($this->context->Api) && Config::SITE_LANGUAGE != null)
                $langFile = CAT($this->context->LocalRoot, "local/", Config::SITE_LANGUAGE, ".txt");
            $this->context->Response->write($val, $langFile);
        }
        else
            $this->printString .= $val->getValue();
    }

    /**
     * Include file with class and generate content by calling method.
     * @param TString $className Class name to include.
     * @param TString $defaultMethod Default method to call.
     * @return TString Resulting content.
     */
    public function includeTemplate($className, $defaultMethod= "execute")
    {
        $engine = $this->context->pushEngine(false);
        $prefix = CAT(Config::FILE_PREFIX, "Bula/Fetcher/Controller/");
        $fileName =
            CAT($prefix, $className, ".php");

        $content = null;
        if (Helper::fileExists(CAT($this->context->LocalRoot, $fileName))) {
            require_once($fileName);
            $args0 = new TArrayList(); $args0->add($this->context);
            Internal::callMethod(CAT($prefix, $className), $args0, $defaultMethod, null);
            $content = $engine->getPrintString();
        }
        else
            $content = CAT("No such file: ", $fileName);
        $this->context->popEngine();
        return $content;
    }

    /**
     * Show template content by merging template and data.
     * @param TString $id Template ID to use for merging.
     * @param THashtable $hash Data in the form of THashtable to use for merging.
     * @return TString Resulting content.
     */
    public function showTemplate($id, $hash = null)
    {
        $ext = BLANK($this->context->Api) ? ".html" : (Config::API_FORMAT == "Xml"? ".xml" : ".txt");
        $prefix = CAT(Config::FILE_PREFIX, "Bula/Fetcher/View/");

        $filename =
                CAT($prefix, (BLANK($this->context->Api) ? "Html/" : (Config::API_FORMAT == "Xml"? "Xml/" : "Rest/")), $id, $ext);
        $template = $this->getTemplate($filename);

        $content = new TString();
        $short_name = Strings::replace("Bula/Fetcher/View/Html", "View", $filename);
        if (!BLANK(Config::FILE_PREFIX))
            $short_name = Strings::replace(Config::FILE_PREFIX, "", $short_name);
        if (BLANK($this->context->Api))
            $content->concat(CAT(EOL, "<!-- BEGIN ", $short_name, " -->", EOL));
        if (!BLANK($template))
            $content->concat($this->processTemplate($template, $hash));
        if (BLANK($this->context->Api))
            $content->concat(CAT("<!-- END ", $short_name, " -->", EOL));
        return $content;
    }

    /**
     * Get template as the list of lines.
     * @param TString $filename File name.
     * @return TArrayList Resulting array with lines.
     */
    private function getTemplate($filename)
    {
        if (Helper::fileExists(CAT($this->context->LocalRoot, $filename))) {
            $lines = Helper::readAllLines(CAT($this->context->LocalRoot, $filename));
            return TArrayList::createFrom($lines);
        }
        else {
            $temp = new TArrayList();
            $temp->add(CAT("File not found -- '", $filename, "'<hr/>"));
            return $temp;
        }
    }

    /**
     * Do actual merging of template and data.
     * @param TString $template Template content.
     * @param THashtable $hash Data for merging with template.
     * @return TString Resulting content.
     */
    public function formatTemplate($template, THashtable $hash)
    {
        if ($hash == null)
            $hash = new THashtable();
        $content1 = Strings::replaceInTemplate($template, $hash);
        $content2 = Strings::replaceInTemplate($content1, $this->context->GlobalConstants);
        return $content2;
    }

    /**
     * Trim comments from input string.
     * @param TString $str Input string.
     * @return TString Resulting string.
     */

    /**
     * Trim comments from input string.
     * @param TString $str Input string.
     * @param Boolean $trim Whether to trim spaces in resulting string.
     * @return TString Resulting string.
     */
    private static function trimComments($str, $trim= true)
    {
        $line = new TString($str);
        $trimmed = false;
        if ($line->indexOf("<!--#") != -1) {
            $line = $line->replace("<!--", "");
            $line = $line->replace("-->", "");
            $trimmed = true;
        }
        else if ($line->indexOf("//#") != -1) {
            $line = $line->replace("//#", "#");
            $trimmed = true;
        }
        if ($trim)
            $line = $line->trim();
        return $line;
    }

    /**
     * Execute template processing.
     * @param TArrayList $template Template in form of the list of lines.
     * @param THashtable $hash Data for merging with template.
     * @return TString Resulting content.
     */
    private function processTemplate(TArrayList $template, THashtable $hash = null)
    {
        if ($this->context->IsMobile) {
            if ($hash == null)
                $hash = new THashtable();
            $hash->put("[#Is_Mobile]", 1);
        }
        $trimLine = true;
        $trimEnd = EOL;
        $ifMode = 0;
        $repeatMode = 0;
        $ifBuf = new TArrayList();
        $repeatBuf = new TArrayList();
        $ifWhat = "";
        $repeatWhat = "";
        $content = new TString();
        for ($n = 0; $n < $template->size(); $n++) {
            $line = $template->get($n);
            $lineNoComments = self::trimComments($line); //, BLANK($this->context->Api)); //TODO
            if ($ifMode > 0) {
                if ($lineNoComments->indexOf("#if") == 0)
                    $ifMode++;
                if ($lineNoComments->indexOf("#end if") == 0) {
                    if ($ifMode == 1) {
                        $not = ($ifWhat->indexOf("!") == 0);
                        $eq = ($ifWhat->indexOf("==") != -1);
                        $neq = ($ifWhat->indexOf("!=") != -1);
                        $processFlag = false;
                        if ($not == true) {
                            if (!$hash->containsKey($ifWhat->substring(1))) //TODO
                                $processFlag = true;
                        }
                        else {
                            if ($eq) {
                                $ifWhatArray = Strings::split("==", $ifWhat);
                                $ifWhat1 = $ifWhatArray[0];
                                $ifWhat2 = $ifWhatArray[1];
                                if ($hash->containsKey($ifWhat1) && EQ($hash->get($ifWhat1), $ifWhat2))
                                    $processFlag = true;
                            }
                            else if ($neq) {
                                $ifWhatArray = Strings::split("!=", $ifWhat);
                                $ifWhat1 = $ifWhatArray[0];
                                $ifWhat2 = $ifWhatArray[1];
                                if ($hash->containsKey($ifWhat1) && !EQ($hash->get($ifWhat1), $ifWhat2))
                                    $processFlag = true;
                            }
                            else if ($hash->containsKey($ifWhat))
                                $processFlag = true;
                        }

                        if ($processFlag)
                            $content->concat(self::processTemplate($ifBuf, $hash));
                        $ifBuf = new TArrayList();
                    }
                    else
                        $ifBuf->add($line);
                    $ifMode--;
                }
                else
                    $ifBuf->add($line);
            }
            else if ($repeatMode > 0) {
                if ($lineNoComments->indexOf("#repeat") == 0)
                    $repeatMode++;
                if ($lineNoComments->indexOf("#end repeat") == 0) {
                    if ($repeatMode == 1) {
                        if ($hash->containsKey($repeatWhat)) {
                            $rows = $hash->get($repeatWhat);
                            for ($r = 0; $r < $rows->size(); $r++)
                                $content->concat(self::processTemplate($repeatBuf, $rows->get($r)));
                            $hash->remove($repeatWhat);
                        }
                        $repeatBuf = new TArrayList();
                    }
                    else
                        $repeatBuf->add($line);
                    $repeatMode--;
                }
                else
                    $repeatBuf->add($line);
            }
            else {
                if ($lineNoComments->indexOf("#if") == 0) {
                    $ifMode = $repeatMode > 0 ? 2 : 1;
                    $ifWhat = $lineNoComments->substring(4)->trim();
                }
                else if ($lineNoComments->indexOf("#repeat") == 0) {
                    $repeatMode++;
                    $repeatWhat = $lineNoComments->substring(8)->trim();
                    $repeatBuf = new TArrayList();
                }
                else {
                    if ($trimLine) {
                        $line = new TString($line);
                        $line = $line->trim();
                        $line->concat($trimEnd);
                    }
                    $content->concat($line);
                }
            }
        }
        $result = self::formatTemplate($content, $hash);
        return $result;
    }
}
