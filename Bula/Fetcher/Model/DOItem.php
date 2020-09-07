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
require_once("Bula/Model/DOBase.php");

/**
 * Manipulating with items.
 */
class DOItem extends DOBase
{
    /** Public constructor (overrides base constructor) */
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "items";
        $this->idField = "i_ItemId";
    }

    /**
     * Get item by ID.
     * @param Integer $itemid ID of the item.
     * @return DataSet Resulting data set.
     */
    public function getById($itemid)
    { // overloaded
        if (!isset($itemid) || $itemid == null) return null;
        if ($itemid <= 0) return null;
        $query = Strings::concat(
            " SELECT _this.*, s.s_SourceName FROM ", $this->tableName, " _this ",
            " LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
            " WHERE _this.", $this->idField, " = ? ");
        $pars = array("setInt", $itemid);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Find item with given link.
     * @param TString $link Link to find.
     * @param Integer $sourceId Source ID to find in (default = 0).
     * @return DataSet Resulting data set.
     */
    public function findItemByLink($link, $sourceId= 0 )
    {
        if ($link == null)
            return null;
        $query = Strings::concat(
            " SELECT _this.", $this->idField, " FROM ", $this->tableName, " _this ",
            //(BLANK($source) ? null : " LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) "),
            " WHERE ", ($sourceId == 0 ? null : " _this.i_SourceLink = ? AND "), "_this.s_Link = ?");
        $pars = array();
        if ($sourceId != 0)
            $pars = array("setInt", $sourceId);
        $pars = ADD($pars, ARR("setString", $link));
        return $this->getDataSet($query, $pars);
    }

    /**
     * Build SQL query from categories filter.
     * @param TString $filter Filter from the category.
     * @return TString Appropriate SQL-query.
     */
    public function buildSqlFilter($filter)
    {
        $filterChunks = Strings::split("~", $filter);
        $includeChunks = SIZE($filterChunks) > 0 ?
            Strings::split("|", $filterChunks[0]) : null;
        $excludeChunks = SIZE($filterChunks) > 1 ?
            Strings::split("|", $filterChunks[1]) : null;
        $includeFilter = new TString();
        for ($n = 0; $n < SIZE($includeChunks); $n++) {
            if (!$includeFilter->isEmpty())
                $includeFilter->concat(" OR ");
            $includeFilter->concat("(_this.s_Title LIKE '%");
                $includeFilter->concat($includeChunks[$n]);
			$includeFilter->concat("%' OR _this.t_FullDescription LIKE '%");
				$includeFilter->concat($includeChunks[$n]);
			$includeFilter->concat("%')");
		}
		if (!$includeFilter->isEmpty())
			$includeFilter = Strings::concat(" (", $includeFilter, ") ");

		$excludeFilter = new TString();
		for ($n = 0; $n < SIZE($excludeChunks); $n++) {
			if (!BLANK($excludeFilter))
				$excludeFilter = Strings::concat($excludeFilter, " AND ");
			$excludeFilter = Strings::concat($excludeFilter,
				"(_this.s_Title not like '%", $excludeChunks[$n], "%' AND _this.t_Description not like '%", $excludeChunks[$n], "%')");
		}
		if (!$excludeFilter->isEmpty())
			$excludeFilter = Strings::concat(" (", $excludeFilter, ") ");

		$realFilter = $includeFilter;
		if (!$excludeFilter->isEmpty())
			$realFilter = CAT($realFilter, " AND ", $excludeFilter);
		return $realFilter;
	}

	/**
     * Enumerate items.
     * @param TString $source Source name to include items from (default - all sources).
     * @param TString $search Filter for the category (or empty).
     * @param Integer $list Include the list No.
     * @param TString $rows List size.
     * @return DataSet Resulting data set.
     */
    public function enumItems($source, $search, $list, $rows)
    { //, $totalRows) {
		$realSearch = BLANK($search) ? null : $this->buildSqlFilter($search);
		$query1 = Strings::concat(
			" SELECT _this.", $this->idField, " FROM ", $this->tableName, " _this ",
			" LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : CAT(" AND s.s_SourceName = '", $source, "' ")),
			(BLANK($realSearch) ? null : CAT(" AND (", $realSearch, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->idField, " DESC "
		);

		$pars1 = array();
		$ds1 = $this->getDataSetList($query1, $pars1, $list, $rows); //, $totalRows);
		if ($ds1->getSize() == 0)
			return $ds1;

		$totalPages = $ds1->getTotalPages();
		$inList = new TString();
		for ($n = 0; $n < $ds1->getSize(); $n++) {
			$o = $ds1->getRow($n);
			if ($n != 0)
				$inList->concat(", ");
            $id = $o->get($this->idField);
			$inList->concat($id);
		}

		$query2 = Strings::concat(
			" SELECT _this.", $this->idField, ", s.s_SourceName, _this.s_Title, _this.s_Url, _this.d_Date, _this.s_Category, ",
			" _this.s_Creator, _this.s_Custom1, _this.s_Custom2, s.s_SourceName ",
			" FROM ", $this->tableName, " _this ",
			" LEFT JOIN sources s ON (s.i_SourceId = _this.i_SourceLink ) ",
			" WHERE _this.", $this->idField, " IN (", $inList, ") ",
			" ORDER BY _this.d_Date DESC, _this.", $this->idField, " DESC "
		);
		$pars2 = array();
		$ds2 = $this->getDataSet($query2, $pars2);
		$ds2->setTotalPages($totalPages);

		return $ds2;
	}

    /**
     * Enumerate items from date.
     * @param TString $fromdate Date to include items starting from.
     * @return DataSet Resulting data set.
     */
    public function enumItemsFromDate($fromdate)
    {
		$query = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->tableName, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE _this.d_Date > ? ",
			" ORDER BY _this.d_Date DESC, _this.", $this->idField, " DESC "
		);
		$pars = array("setDate", $fromdate);
		return $this->getDataSet($query, $pars);

	}

	/**
     * Enumerate items from given date.
     * @param TString $fromDate Date to include items starting from.
     * @param TString $source Source name to include items from (default - all sources).
     * @param TString $filter Filter for the category (or empty - no filtering).
     * @param type $maxItems Max number of returned items.
     * @return DataSet Resulting data set.
     */
    public function enumItemsFromSource($fromDate, $source, $filter, $maxItems= 20 )
    {
		$realFilter = BLANK($filter) ? null : $this->buildSqlFilter($filter);
		$query1 = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->tableName, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : Strings::concat(" AND s.s_SourceName = '", $source, "' ")),
			(BLANK($realFilter) ? null : Strings::concat(" AND (", $realFilter, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->idField, " DESC ",
			" LIMIT ", $maxItems
		);
		$pars1 = array();
		$ds1 = $this->getDataSet($query1, $pars1);
        if ($fromDate == null)
            return $ds1;

		$query2 = Strings::concat(
			" SELECT _this.*, s.s_SourceName FROM ", $this->tableName, " _this ",
			" INNER JOIN sources s ON (s.i_SourceId = _this.i_SourceLink) ",
			" WHERE s.b_SourceActive = 1 ",
			(BLANK($source) ? null : Strings::concat(" AND s.s_SourceName = '", $source, "' ")),
			" AND _this.d_Date > ? ",
			(BLANK($realFilter) ? null : Strings::concat(" AND (", $realFilter, ") ")),
			" ORDER BY _this.d_Date DESC, _this.", $this->idField, " DESC ",
			" LIMIT ", $maxItems
		);
		$pars2 = array("setDate", $fromDate);
		$ds2 = $this->getDataSet($query2, $pars2);

		return $ds1->getSize() > $ds2->getSize() ? $ds1 : $ds2;
	}

    /**
     * Purge items.
     * @param Integer $days Remove items older than $days.
     * @return DataSet Resulting data set.
     */
    public function purgeOldItems($days)
    {
		$purgeDate = DateTimes::format(DBConfig::SQL_DTS, DateTimes::getTime(CAT("-", $days, " days")));
		$query = Strings::concat("DELETE FROM ", $this->tableName, " WHERE d_Date < ?");
		$pars = array("setDate", $purgeDate);

		return $this->updateInternal($query, $pars, "update");
	}
}
