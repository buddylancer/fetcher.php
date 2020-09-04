<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Model;

use Bula\Fetcher\Config;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Objects\TString;
use Bula\Objects\Strings;
use Bula\Model\DOBase;
use Bula\Model\DataSet;

require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Model/DOBase.php");

/**
 * Manipulating with categories.
 */
class DOCategory extends DOBase {
    /** Public constructor (overrides base constructor) */
    public function __construct(){
        parent::__construct();
        $this->table_name = "categories";
        $this->id_field = "s_CatId";
    }

    /**
     * Get category by ID.
     * @param TString $catid Category ID.
     * @return DataSet Resulting data set.
     */
    public function getCategoryById($catid) {
        if (BLANK($catid))
            return null;
        $query = Strings::concat(
            " SELECT * FROM ", $this->table_name, " _this " ,
            " WHERE _this.", $this->id_field, " = ? ");
        $pars = array("setString", $catid);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get category by name.
     * @param TString $catname Category name.
     * @return DataSet Resulting data set.
     */
    public function getCategoryByName($catname) {
        if (BLANK($catname))
            return null;
        $query = Strings::concat(
            " SELECT * FROM ", $this->table_name, " _this ",
            " WHERE _this.s_Name = ? ");
        $pars = array("setString", $catname);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Enumerate categories.
     * @param TString $order Field name to sort result by (default = null).
     * @param type $min_count Include categories with Counter >= min_count.
     * @param type $limit Include not more than "limit" records (default = no limit).
     * @return DataSet Resulting data set.
     */
    public function enumCategories($order = null, $min_count = 0, $limit = 0) {
        if ($min_count < 0)
            return null;
        $query = Strings::concat(
            " SELECT * FROM ", $this->table_name, " _this ",
            ($min_count > 0 ? CAT(" WHERE _this.i_Counter > ", $min_count) : null),
            " ORDER BY ", (EQ($order, "counter") ? " _this.i_Counter desc " : " _this.s_CatId asc "),
            ($limit == 0 ? null : CAT(" LIMIT ", $limit))
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Check whether category (filter) exists.
     * @param TString $filter_name Category ID.
     * @param Object[] $category Category object (if found) copied to element 0 of object array.
     * @return boolean True if exists.
     */
    public function checkFilterName($filter_name, &$category = null ) {
		$dsCategories = $this->select("_this.s_CatId, _this.s_Filter");
		$filter_found = false;
		for ($n = 0; $n < $dsCategories->getSize(); $n++) {
			$oCategory = $dsCategories->getRow($n);
			if (EQ($oCategory->get("s_CatId"), $filter_name)) {
				$filter_found = true;
				if ($category != null)
                    $category[0] = $oCategory;
				break;
			}
		}
		return $filter_found;
	}
}
