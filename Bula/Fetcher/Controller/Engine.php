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
class Engine {
    private $context = null;
    private $print_flag = false;
    private $print_string = "";

    /** Public default constructor */
    public function __construct($context) {
        $this->context = $context;
        $this->print_flag = false;
        $this->print_string = "";
    }

    /**
     * Set print string for current engine instance.
     * @param TString $val Print string to set.
     */
    public function setPrintString($val) {
        $this->print_string = $val;
    }

    /**
     * Get print string for current engine instance.
     * @return TString Current print string.
     */
    public function getPrintString() {
        return $this->print_string;
    }

    /**
     * Set print flag for current engine instance.
     * @param Boolean $val Print flag to set.
     */
    public function setPrintFlag($val) {
        $this->print_flag = $val;
    }

    /**
     * Get print flag for current engine instance.
     * @return Boolean Current print flag.
     */
    public function getPrintFlag() {
        return $this->print_flag;
    }

    /**
     * Write string.
     * @param TString $val String to write.
     */
    public function write($val) {
        if ($this->print_flag)
            Response::write($val);
        else
            $this->print_string .= $val->getValue();
    }

    /**
     * Include file with class and generate content by calling method execute().
     * @param TString $class_name Class name to include.
     * @return TString Resulting content.
     */
    public function includeTemplate($class_name, $default_method = "execute") {
        $engine = $this->context->pushEngine(false);
        $file_name = 
            CAT($class_name, ".php");

        $content = null;
        if (Helper::fileExists(CAT($this->context->LocalRoot, $file_name))) {
            require_once($file_name);
            $args0 = new ArrayList(); $args0->add($this->context);
            Internal::callMethod($class_name, $args0, "execute", null);
            $content = $engine->getPrintString();
        }
        else
            $content = CAT("No such file: ", $file_name);
        $this->context->popEngine();
        return $content;
    }

    /**
     * Show template content by merging template and data.
     * @param TString $filename Template file to use for merging.
     * @param Hashtable $hash Data in the form of Hashtable to use for merging.
     * @return TString Resulting content.
     */
    public function showTemplate($filename, $hash = null) {
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
    private function getTemplate($filename) {
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
    public function formatTemplate($template, Hashtable $hash) {
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
    private static function trimComments($str) {
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
    private function processTemplate(ArrayList $template, Hashtable $hash = null) {
        if ($this->context->IsMobile) {
            if ($hash == null)
                $hash = new Hashtable();
            $hash->put("[#Is_Mobile]", 1);
        }
        $trim_line = true;
        $trim_end = "\n";
        $if_mode = 0;
        $repeat_mode = 0;
        $if_buf = new ArrayList();
        $repeat_buf = new ArrayList();
        $if_what = "";
        $repeat_what = "";
        $content = new TString();
        for ($n = 0; $n < $template->count(); $n++) {
            $line = /*(TString)*/$template->get($n);
            $line_no_comments = self::trimComments($line);
            if ($if_mode > 0) {
                if ($line_no_comments->indexOf("#if") == 0)
                    $if_mode++;
                if ($line_no_comments->indexOf("#end if") == 0) {
                    if ($if_mode == 1) {
                        $not = ($if_what->indexOf("!") == 0);
                        $eq = ($if_what->indexOf("==") != -1);
                        $neq = ($if_what->indexOf("!=") != -1);
                        $process_flag = false;
                        if ($not == true) {
                            if (!$hash->containsKey($if_what->substring(1))) //TODO
                                $process_flag = true;
                        }
                        else {
                            if ($eq) {
                                $if_what_array = Strings::split("==", $if_what);
                                $if_what_1 = $if_what_array[0];
                                $if_what_2 = $if_what_array[1];
                                if ($hash->containsKey($if_what_1) && EQ($hash->get($if_what_1), $if_what_2))
                                    $process_flag = true;
                            }
                            else if ($neq) {
                                $if_what_array = Strings::split("!=", $if_what);
                                $if_what_1 = $if_what_array[0];
                                $if_what_2 = $if_what_array[1];
                                if ($hash->containsKey($if_what_1) && !EQ($hash->get($if_what_1), $if_what_2))
                                    $process_flag = true;
                            }
                            else if ($hash->containsKey($if_what))
                                $process_flag = true;
                        }

                        if ($process_flag)
                            $content->concat(self::processTemplate($if_buf, $hash));
                        $if_buf = new ArrayList();
                    }
                    else
                        $if_buf->add($line);
                    $if_mode--;
                }
                else
                    $if_buf->add($line);
            }
            else if ($repeat_mode > 0) {
                if ($line_no_comments->indexOf("#repeat") == 0)
                    $repeat_mode++;
                if ($line_no_comments->indexOf("#end repeat") == 0) {
                    if ($repeat_mode == 1) {
                        if ($hash->containsKey($repeat_what)) {
                            $rows = /*(ArrayList)*/$hash->get($repeat_what);
                            for ($r = 0; $r < $rows->count(); $r++)
                                $content->concat(self::processTemplate($repeat_buf, /*(Hashtable)*/$rows->get($r)));
                            $hash->remove($repeat_what);
                        }
                        $repeat_buf = new ArrayList();
                    }
                    else
                        $repeat_buf->add($line);
                    $repeat_mode--;
                }
                else
                    $repeat_buf->add($line);
            }
            else {
                if ($line_no_comments->indexOf("#if") == 0) {
                    $if_mode = $repeat_mode > 0 ? 2 : 1;
                    $if_what = $line_no_comments->substring(4)->trim();
                }
                else if ($line_no_comments->indexOf("#repeat") == 0) {
                    $repeat_mode++;
                    $repeat_what = $line_no_comments->substring(8)->trim();
                    $repeat_buf = new ArrayList();
                }
                else {
                    if ($trim_line) {
                        $line = new TString($line);
                        $line = $line->trim();
                        $line->concat($trim_end);
                    }
                    $content->concat($line);
                }
            }
        }
        $result = self::formatTemplate($content->getValue(), $hash);
        return $result;
    }
}
