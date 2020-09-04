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
use Bula\Objects\Hashtable;
use Bula\Objects\TString;
use Bula\Objects\Strings;
use Bula\Model\DBConfig;
use Bula\Model\DOBase;
use Bula\Model\DataSet;

require_once("Bula/Objects/TString.php");
require_once("Bula/Model/DOBAse.php");

/**
 * Manipulating with items.
 */
class DOItem extends DOBase {
    /** Public constructor (overrides base constructor) */
    public function __construct(){
        parent::__construct();
        $this->table_name = "items";
        $this->id_field = "i_ItemId";
    }

    /**
     * Get item by ID.
     * @param Integer $itemid ID of the item.
     * @return DataSet Resulting data set.
     */
    public function getById($itemid) { // overloaded
        if (!isset($itemid) || $itemid == null) return null;
        if ($itemid <= 0) return null;
        $query = Strings::concat(
            " SELECT _this.*, s.s_SourceName FROM ", $this->table_name, " _this ",
            " LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
            " WHERE _this.", $this->id_field, " = ? ");
        $pars = array("setInt", $itemid);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Find item with given link.
     * @param TString $link Link to find.
     * @param Integer $source_id Source ID to find in (default = 0).
     * @return DataSet Resulting data set.
     */
    public function findItemByLink($link, $source_id= 0 ) {
        if ($link == null)
            return null;
        $query = Strings::concat(
            " SELECT _this.", $this->id_field, " FROM ", $this->table_name, " _this ",
            //(BLANK($source) ? null : " LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) "),
            " WHERE ", ($source_id == 0 ? null : " _this.i_SourceLink = ? AND "), "_this.s_Link = ?");
        $pars = array();
        if ($source_id != 0)
            $pars = array("setInt", $source_id);
        $pars = ADD($pars, ARR("setString", $link));
        return $this->getDataSet($query, $pars);
    }

    /**
     * Build SQL query from categories filter.
     * @param TString $filter Filter from the category.
     * @return TString Appropriate SQL-query.
     */
    public function buildSqlFilter($filter) {
        $filter_chunks = Strings::split("~", $filter);
        $include_chunks = SIZE($filter_chunks) > 0 ?
            Strings::split("|", $filter_chunks[0]) : null;
        $exclude_chunks = SIZE($filter_chunks) > 1 ?
            Strings::split("|", $filter_chunks[1]) : null;
        $include_filter = new TString();
        for ($n = 0; $n < SIZE($include_chunks); $n++) {
            if (!$include_filter->isEmpty())
                $include_filter->concat(" OR ");
            $include_filter->concat("(_this.s_Title LIKE '%");
                $include_filter->concat($include_chunks[$n]);
			$include_filter->concat("%' OR _this.t_FullDescription LIKE '%");
				$include_filter->concat($include_chunks[$n]);
			$include_filter->concat("%')");
		}
		if (!$include_filter->isEmpty())
			$include_filter = Strings::concat(" (", $include_filter, ") ");

		$exclude_filter = new TString();
		for ($n = 0; $n < SIZE($exclude_chunks); $n++) {
			if (!BLANK($exclude_filter))
				$exclude_filter = Strings::concat($exclude_filter, " AND ");
			$exclude_filter = Strings::concat($exclude_filter,
				"(_this.s_Title not like '%", $exclude_chunks[$n], "%' AND _this.t_Description not like '%", $exclude_chunks[$n], "%')");
		}
		if (!$exclude_filter->isEmpty())
			$exclude_filter = Strings::concat(" (", $exclude_filter, ") ");

		$real_filter = $include_filter;
		if (!$exclude_filter->isEmpty())
			$real_filter = CAT($real_filter, " AND ", $exclude_filter);
		return $real_filter;
	}

	/**
     * Enumerate items.
     * @param TString $source Source name to include items from (default - all sources).
     * @param TString $search Filter for the category (or empty).
     * @param Integer $list Include the list No.
     * @param TString $rows List size.
     * @return DataSet Resulting data set.
     */
    public function enumItems($source, $search, $list, $rows) { //, $total_rows) {
		$real_search = BLANK($search) ? null : $this->buildSqlFilter($search);
		$query1 = Strings::concat(
			" SELECT _this.", $this->id_field, " FROM ", $this->table_name, " _this ",
			" LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : CAT(" AND s.s_SourceName = '", $source, "' ")),
			(BLANK($real_search) ? null : CAT(" AND (", $real_search, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->id_field, " DESC "
		);

		$pars1 = array();
		$ds1 = $this->getDataSetList($query1, $pars1, $list, $rows); //, $total_rows);
		if ($ds1->getSize() == 0)
			return $ds1;

		$total_pages = $ds1->getTotalPages();
		$in_list = new TString();
		for ($n = 0; $n < $ds1->getSize(); $n++) {
			$o = $ds1->getRow($n);
			if ($n != 0)
				$in_list->concat(", ");
            $id = $o->get($this->id_field);
			$in_list->concat($id);
		}

		$query2 = Strings::concat(
			" SELECT _this.", $this->id_field, ", s.s_SourceName, _this.s_Title, _this.s_Url, _this.d_Date, _this.s_Category, ",
			" _this.s_Creator, _this.s_Custom1, _this.s_Custom2, s.s_SourceName ",
			" FROM ", $this->table_name, " _this ",
			" LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink ) ",
			" WHERE _this.", $this->id_field, " IN (", $in_list, ") ",
			" ORDER BY _this.d_Date DESC, _this.", $this->id_field, " DESC "
		);
		$pars2 = array();
		$ds2 = $this->getDataSet($query2, $pars2);
		$ds2->setTotalPages($total_pages);

		return $ds2;
	}

    /**
     * Enumerate items from date.
     * @param TString $fromdate Date to include items starting from.
     * @return DataSet Resulting data set.
     */
    public function enumItemsFromDate($fromdate) {
		$query = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->table_name, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE _this.d_Date > ? ",
			" ORDER BY _this.d_Date DESC, _this.", $this->id_field, " DESC "
		);
		$pars = array("setDate", $fromdate);
		return $this->getDataSet($query, $pars);

	}

	/**
     * Enumerate items from given date.
     * @param TString $from_date Date to include items starting from.
     * @param TString $source Source name to include items from (default - all sources).
     * @param TString $filter Filter for the category (or empty - no filtering).
     * @param type $max_items Max number of returned items.
     * @return DataSet Resulting data set.
     */
    public function enumItemsFromSource($from_date, $source, $filter, $max_items= 20 ) {
		$real_filter = BLANK($filter) ? null : $this->buildSqlFilter($filter);
		$query1 = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->table_name, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : Strings::concat(" AND s.s_SourceName = '", $source, "' ")),
			(BLANK($real_filter) ? null : Strings::concat(" AND (", $real_filter, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->id_field, " DESC ",
			" LIMIT ", $max_items
		);
		$pars1 = array();
		$ds1 = $this->getDataSet($query1, $pars1);
        if ($from_date == null)
            return $ds1;

		$query2 = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->table_name, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : Strings::concat(" AND s.s_SourceName = '", $source, "' ")),
			" AND _this.d_Date > ? ",
			(BLANK($real_filter) ? null : Strings::concat(" AND (", $real_filter, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->id_field, " DESC ",
			" LIMIT ", $max_items
		);
		$pars2 = array("setDate", $from_date);
		$ds2 = $this->getDataSet($query2, $pars2);

		return $ds1->getSize() > $ds2->getSize() ? $ds1 : $ds2;
	}

    /**
     * Purge items.
     * @param Integer $days Remove items older than $days.
     * @return DataSet Resulting data set.
     */
    public function purgeOldItems($days) {
		$purge_date = DateTimes::format(DBConfig::SQL_DTS, DateTimes::getTime(CAT("-", $days, " days")));
		$query = Strings::concat("DELETE FROM ", $this->table_name, " WHERE d_Date < ?");
		$pars = array("setDate", $purge_date);

		return $this->updateInternal($query, $pars, "update");
	}
}
