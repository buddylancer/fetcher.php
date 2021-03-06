<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Model;

use Bula\Objects\TArrayList;
use Bula\Objects\TEnumerator;
use Bula\Objects\THashtable;
use Bula\Objects\TString;

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/TEnumerator.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");

/**
 * Non-typed data set implementation.
 */
class DataSet
{
    private $rows;
    private $pageSize;
    private $totalPages;

    /** Default public constructor */
    public function __construct()
    {
        $this->rows = new TArrayList();
        $this->pageSize = 10;
        $this->totalPages = 0;
    }

    /**
     * Get the size (number of rows) of the DataSet.
     * @return Integer DataSet size.
     */
    public function getSize()
    {
        return $this->rows->size();
    }

    /**
     * Get a row from the DataSet.
     * @param Integer $n Number of the row.
     * @return THashtable Required row or null.
     */
    public function getRow($n)
    {
        return $this->rows->get($n);
    }

    /**
     * Add new row into the DataSet.
     * @param THashtable $row New row to add.
     */
    public function addRow($row)
    {
        $this->rows->add($row);
    }

    /**
     * Get page size of the DataSet.
     * @return Integer Current page size.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set page size of the DataSet.
     * @param Integer pageSize Current page size.
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Get total number of pages in the DataSet.
     * @return Integer Number of pages.
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Set total number of pages in the DataSet.
     * @param Integer $totalPages Number of pages.
     */
    public function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;
    }

    private function addSpaces($level)
    {
        $spaces = new TString();
        for ($n = 0; $n < $level; $n++)
            $spaces->concat("    ");
        return $spaces;
    }

    /**
     * Get serialized (XML) representation of the DataSet.
     * @return TString Resulting representation.
     */
    public function toXml($EOL)
    {
        $level = 0;
        $spaces = null;
        $output = new TString();
        $output->concat(CAT("<DataSet Rows=\"", $this->rows->size(), "\">", $EOL));
        for ($n = 0; $n < $this->getSize(); $n++) {
            $row = $this->getRow($n);
            $level++; $spaces = $this->addSpaces($level);
            $output->concat(CAT($spaces, "<Row>", $EOL));
            $keys = new TEnumerator($row->keys());
            while ($keys->moveNext()) {
                $level++; $spaces = $this->addSpaces($level);
                $key = $keys->getCurrent();
                $value = $row->get($key);
                if (NUL($value)) {
                    $output->concat(CAT($spaces, "<Item Name=\"", $key, "\" IsNull=\"True\" />", EOL));
                }
                else {
                    $output->concat(CAT($spaces, "<Item Name=\"", $key, "\">"));
                    $output->concat($row->get($key));
                    $output->concat(CAT("</Item>", $EOL));
                }
                $level--; $spaces = $this->addSpaces($level);
            }
            $output->concat(CAT($spaces, "</Row>", $EOL));
            $level--; $spaces = $this->addSpaces($level);
        }
        $output->concat(CAT("</DataSet>", $EOL));
        return $output;
    }
}
