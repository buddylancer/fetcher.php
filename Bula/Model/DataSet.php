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
use Bula\Objects\TString;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");

/**
 * Non-typed data set implementation.
 */
class DataSet {
	private $rows;
	private $page_size;
	private $total_pages;

	/** Default public constructor */
    public function __construct() {
		$this->rows = new ArrayList();
		$this->page_size = 10;
		$this->total_pages = 0;
	}

    /**
     * Get the size (number of rows) of the DataSet.
     * @return Integer 
     */
	public function getSize() {
        return $this->rows->count();
    }

	/**
     * Get a row from the DataSet.
     * @param Integer $n Number of the row.
     * @return Hashtable Required row or null.
     */
    public function getRow($n) {
        return $this->rows->get($n);
    }

	/**
     * Add new row into the DataSet.
     * @param Hashtable $row New row to add.
     */
    public function addRow($row) {
        $this->rows->add($row);
    }

	/**
     * Get page size of the DataSet.
     * @return Integer Current page size.
     */
    public function getPageSize() {
        return $this->page_size;
    }

	/**
     * Set page size of the DataSet.
     * @param Integer page_size Current page size.
     */
	public function setPageSize($page_size) {
        $this->page_size = $page_size;
    }

	/**
     * Get total number of pages in the DataSet.
     * @return Integer Number of pages.
     */
    public function getTotalPages() {
        return $this->total_pages;
    }

	/**
     * Set total number of pages in the DataSet.
     * @param Integer $total_pages Number of pages.
     */
    public function setTotalPages($total_pages) {
        $this->total_pages = $total_pages;
    }

    private function addSpaces($level) {
        $spaces = new TString();
        for ($n = 0; $n < $level; $n++)
            $spaces->concat("    ");
        return $spaces;
    }

    /**
     * Get serialized (XML) representation of the DataSet.
     * @return TString Resulting representation.
     */
    public function toXml() {
        $level = 0;
        $spaces = null;
        $output = new TString();
        $output->concat(CAT("<DataSet Rows=\"", $this->rows->count(), "\">\n"));
        for ($n = 0; $n < $this->getSize(); $n++) {
            $row = $this->getRow($n);
            $level++; $spaces = $this->addSpaces($level);
            $output->concat(CAT($spaces, "<Row>\n"));
            $keys = $row->keys();
            while ($keys->moveNext()) {
                $level++; $spaces = $this->addSpaces($level);
                $key = /*(TString)*/$keys->current();
                $output->concat(CAT($spaces, "<Item Name=\"", $key, "\">"));
                $output->concat($row->get($key));
                $output->concat("</Item>\n");
                $level--; $spaces = $this->addSpaces($level);
            }
            $output->concat(CAT($spaces, "</Row>\n"));
            $level--; $spaces = $this->addSpaces($level);
        }
        $output->concat("</DataSet>\n");
        return $output;
    }
}
