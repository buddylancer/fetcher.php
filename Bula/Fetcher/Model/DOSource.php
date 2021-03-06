<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Model;

use Bula\Fetcher\Config;
use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
use Bula\Objects\TString;
use Bula\Objects\Strings;

use Bula\Model\Connection;
use Bula\Model\DOBase;
use Bula\Model\DataSet;

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");

require_once("Bula/Model/Connection.php");
require_once("Bula/Model/DOBase.php");
require_once("Bula/Model/DataSet.php");

/**
 * Manipulations with sources.
 */
class DOSource extends DOBase
{
    /** Public constructor (overrides base constructor) */
    public function __construct($connection)
    {
        parent::__construct($connection);
        $this->tableName = "sources";
        $this->idField = "i_SourceId";
    }

    /**
     * Enumerates all sources.
     * @return DataSet Resulting data set.
     */
    public function enumSources()
    {
        $query = Strings::concat(
            " SELECT _this.* FROM ", $this->tableName, " _this ",
            " where _this.b_SourceActive = 1 ",
            " order by _this.s_SourceName asc"
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Enumerates sources, which are active for fetching.
     * @return DataSet Resulting data set.
     */
    public function enumFetchedSources()
    {
        $query = Strings::concat(
            " SELECT _this.* FROM ", $this->tableName, " _this ",
            " where _this.b_SourceFetched = 1 ",
            " order by _this.s_SourceName asc"
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Enumerates all sources with counters.
     * @return DataSet Resulting data set.
     */
    public function enumSourcesWithCounters()
    {
        $query = Strings::concat(
            " select _this.", $this->idField, ", _this.s_SourceName, ",
            " count(p.i_SourceLink) as cntpro ",
            " from ", $this->tableName, " _this ",
            " left outer join items p on (p.i_SourceLink = _this.i_SourceId) ",
            " where _this.b_SourceActive = 1 ",
            " group by _this.i_SourceId ",
            " order by _this.s_SourceName asc "
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get source by ID.
     * @param Integer $sourceid Source ID.
     * @return DataSet Resulting data set.
     */
    public function getSourceById($sourceid)
    {
        if (!isset($sourceid) || $sourceid == null) return null;
        if ($sourceid <= 0) return null;
        $query = Strings::concat("SELECT * FROM sources where i_SourceId = ?");
        $pars = array("setInt", $sourceid);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get source by name.
     * @param TString $sourcename Source name.
     * @return DataSet Resulting data set.
     */
    public function getSourceByName($sourcename)
    {
        if (!isset($sourcename)) return null;
        if ($sourcename == null || $sourcename == "") return null;
        $query = Strings::concat("SELECT * FROM sources where s_SourceName = ?");
        $pars = array("setString", $sourcename);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Check whether source exists.
     * @param TString $sourcename Source name.
     * @param Object[] $source Source object (if found) copied to element 0 of object array.
     * @return boolean True if exists.
     */
    public function checkSourceName($sourcename, &$source = null )
    {
        $dsSources = $this->enumSources();
        $sourceFound = false;
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);
            if (EQ($oSource->get("s_SourceName"), $sourcename)) {
                $sourceFound = true;
                if ($source != null)
                    $source[0] = $oSource;
                break;
            }
        }
        return $sourceFound;
    }
}
