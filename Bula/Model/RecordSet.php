<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Model;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

use Bula\Objects\Arrays;

require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");

/**
 * Implement operations with record sets.
 */
class RecordSet
{
    /** Current result */
    public $result = null;
    /** Current record */
    public $record = null;

    private $numRows = 0;
    private $numPages = 0;
    private $pageRows = 10;
    private $pageNo = 0;

    /** Public constructor */
    public function __construct()
    {
        $this->numRows = 0;
        $this->numPages = 0;
        $this->pageRows = 10;
        $this->pageNo = 0;
    }

    /**
     * Set number of page rows in record set.
     * @param Integer $no Number of rows.
     */
    public function setPageRows($no)
    {
        $this->pageRows = $no;
    }

    /**
     * Set current number of rows (and pages) in the record set.
     * @param Integer $no Number of rows.
     */
    public function setRows($no)
    {
        $this->numRows = $no;
        $this->numPages = INT(($no - 1) / $this->pageRows) + 1;
    }

    /**
     * Get current number of rows in the record set.
     * @return Integer Number of rows.
     */
    public function getRows()
    {
        return $this->numRows;
    }

    /**
     * Get current number of pages in the record set.
     * @return Integer Number of pages.
     */
    public function getPages()
    {
        return $this->numPages;
    }

    /**
     * Set current page of the record set.
     * @param Integer $no Current page.
     */
    public function setPage($no)
    {
        $this->pageNo = $no;
        if ($no != 1) {
            $n = ($no - 1) * $this->pageRows;
            while ($n-- > 0)
                $this->next();
        }
    }

    /**
     * Get current page of the record set.
     * @return Integer Current page number.
     */
    public function getPage()
    {
        return $this->pageNo;
    }

    /**
     * Get next record from the result of operation.
     * @return Integer Status of operation:
     *   1 - next record exists.
     *   0 - next record not exists.
     */
    public function next()
    {
        $arr = DataAccess::fetchArray($this->result);

        if ($arr != null) {
            $this->record = Arrays::createHashtable($arr);
            return 1;
        }
        else
            return 0;
    }

    /**
     * Get value from the record.
     * @param Integer $par Number of value.
     * @return Object
     */
    public function getValue($par)
    {
        return $this->record->get($par);
    }

    /**
     * Get TString value from the record.
     * @param Integer $par Number of value.
     * @return TString
     */
    public function getString($par)
    {
        return $this->record->get($par);
    }

    /**
     * Get DateTime value from the record.
     * @param Integer $par Number of value.
     * @return TString
     */
    public function getDate($par)
    {
        return $this->record->get($par);
    }

    /**
     * Get integer value from the record.
     * @param Integer $par Number of value.
     * @return Integer
     */
    public function getInt($par)
    {
        return INT($this->record->get($par));
    }

    /**
     * Get real value from the record.
     * @param Integer $par Number of value.
     * @return Double
     */
    public function getFloat($par)
    {
        return FLOAT($this->record->get($par));
    }

    /**
     * Close this record set.
     */
    public function close()
    {
        DataAccess::freeResult($this->result);
    }
}

