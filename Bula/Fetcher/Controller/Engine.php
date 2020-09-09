<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Internal;

use Bula\Fetcher\Config;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

use Bula\Objects\Arrays;
use Bula\Objects\Helper;
use Bula\Objects\Strings;
use Bula\Objects\TString;
use Bula\Objects\Response;

require_once("Bula/Internal.php");
require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/TString.php");

/**
 * Engine for processing templates.
 */
class Engine
{
    private $context = null;
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
        if ($this->printFlag)
            Response::write($val);
        else
            $this->printString .= $val->getValue();
    }

    /**
     * Include file with class and generate content by calling method execute().
     * @param TString $className Class name to include.
     * @param TString $defaultMethod Default method to call.
     * @return TString Resulting content.
     */
    public function includeTemplate($className, $defaultMethod = "execute")
    {
        $engine = $this->context->pushEngine(false);
        $fileName =
            CAT($className, ".php");

        $content = null;
        if (Helper::fileExists(CAT($this->context->LocalRoot, $fileName))) {
            require_once($fileName);
            $args0 = new ArrayList(); $args0->add($this->context);
            Internal::callMethod($className, $args0, $defaultMethod, null);
            $content = $engine->getPrintString();
        }
        else
            $content = CAT("No such file: ", $fileName);
        $this->context->popEngine();
        return $content;
    }

    /**
     * Show template content by merging template and data.
     * @param TString $filename Template file to use for merging.
     * @param Hashtable $hash Data in the form of Hashtable to use for merging.
     * @return TString Resulting content.
     */
    public function showTemplate($filename, $hash = null)
    {
        $template = $this->getTemplate($filename);

        $content = new TString();
        $content->concat(CAT("\n<!-- BEGIN ", Strings::replace("Bula/Fetcher/", "", $filename), " -->\n"));
        $content->concat($this->processTemplate($template, $hash));
        $content->concat(CAT("<!-- END ", Strings::replace("Bula/Fetcher/", "", $filename), " -->\n"));
        return $content;
    }

    /**
     * Get template as the list of lines.
     * @param TString $filename File name.
     * @return ArrayList Resulting array with lines.
     */
    private function getTemplate($filename)
    {
        if (Helper::fileExists(CAT($this->context->LocalRoot, $filename))) {
            $lines = Helper::readAllLines(CAT($this->context->LocalRoot, $filename));
            return Arrays::createArrayList($lines);
        }
        else {
            $temp = new ArrayList();
            $temp->add(CAT("File nor found -- '", $filename, "'<hr/>"));
            return $temp;
        }
    }

    /**
     * Do actual merging of template and data.
     * @param TString $template Template content.
     * @param Hashtable $hash Data for merging with template.
     * @return TString Resulting content.
     */
    public function formatTemplate($template, Hashtable $hash)
    {
        if ($hash == null)
            $hash = new Hashtable();
        $content = Strings::replaceInTemplate($template, $hash);
        return Strings::replaceInTemplate($content, $this->context->GlobalConstants);
    }

    /**
     * Trim comments from input string.
     * @param TString $str Input string.
     * @return TString Resulting string.
     */
    private static function trimComments($str)
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
        $line = $line->trim();
        return $line;
    }

    /**
     * Execute template processing.
     * @param ArrayList $template Template in form of the list of lines.
     * @param Hashtable $hash Data for merging with template.
     * @return TString Resulting content.
     */
    private function processTemplate(ArrayList $template, Hashtable $hash = null)
    {
        if ($this->context->IsMobile) {
            if ($hash == null)
                $hash = new Hashtable();
            $hash->put("[#Is_Mobile]", 1);
        }
        $trimLine = true;
        $trimEnd = "\n";
        $ifMode = 0;
        $repeatMode = 0;
        $ifBuf = new ArrayList();
        $repeatBuf = new ArrayList();
        $ifWhat = "";
        $repeatWhat = "";
        $content = new TString();
        for ($n = 0; $n < $template->count(); $n++) {
            $line = $template->get($n);
            $lineNoComments = self::trimComments($line);
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
                        $ifBuf = new ArrayList();
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
                            for ($r = 0; $r < $rows->count(); $r++)
                                $content->concat(self::processTemplate($repeatBuf, $rows->get($r)));
                            $hash->remove($repeatWhat);
                        }
                        $repeatBuf = new ArrayList();
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
                    $repeatBuf = new ArrayList();
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
        $result = self::formatTemplate($content->getValue(), $hash);
        return $result;
    }
}
