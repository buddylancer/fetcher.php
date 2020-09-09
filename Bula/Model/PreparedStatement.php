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

use Bula\Objects\Response;
use Bula\Objects\DateTimes;
use Bula\Objects\Strings;
use Bula\Objects\TString;

require_once("Bula/Objects/Response.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/TString.php");
require_once("RecordSet.php");

/**
 * Implement operations with prepared statement.
 */
class PreparedStatement
{
    /** Link to database instance */
    private $link;
    /** Initial SQL-query */
    private $sql;
    /** List of parameters */
    private $pars;
    /** Formed (prepared) SQL-query */
    private $query;

    /**
     * Resulting record set of the last operation.
     * @var RecordSet
     */
    public $recordSet;

	/** Default public constructor */
    public function __construct()
    {
        $this->pars = new ArrayList();
        $this->pars->add("dummy"); // Parameter number will start from 1.
    }

    /**
     * Execute selection query.
     * @return RecordSet
     */
    public function executeQuery()
    {
        $this->recordSet = new RecordSet();
        if ($this->formQuery()) {
            DataAccess::callPrintDelegate(CAT("Executing selection query [", $this->query, "] ..."));
            $result = DataAccess::selectQuery($this->link, $this->query->getValue());
            if ($result == null || $result == false) {
                DataAccess::callErrorDelegate(CAT("Selection query failed [", $this->query, "]"));
                return null;
            }
            $this->recordSet->result = $result;
            $this->recordSet->setRows(DataAccess::numRows($result));
            $this->recordSet->setPage(1);
            return $this->recordSet;
        }
        else {
            DataAccess::callErrorDelegate(CAT("Error in query: ", $this->query, "<hr/>"));
            return null;
        }
    }

    /**
     * Execute updating query.
     * @return Integer
     *   -1 - error during form query.
     *   -2 - error during execution.
     */
    public function executeUpdate()
    {
        if ($this->formQuery()) {
            DataAccess::callPrintDelegate(CAT("Executing update query [", $this->query, "] ..."));
            $result = DataAccess::updateQuery($this->link, $this->query->getValue());
            if ($result == null) {
                DataAccess::callErrorDelegate(CAT("Query update failed [", $this->query, "]"));
                return -2;
            }
            $ret = DataAccess::affectedRows($this->link);
            return $ret;
        }
        else {
            DataAccess::callErrorDelegate(CAT("Error in update query [", $this->query, "]"));
            return -1;
        }
    }

    /**
     * Get ID for just inserted record.
     * @return Integer
     */
    public function getInsertId()
    {
        return DataAccess::insertId($this->link);
    }

    /**
     * Form query (replace '?' marks with real parameters).
     * @return Boolean
     */
    private function formQuery()
    {
        $questionIndex = -1;
        $startFrom = 0;
        $n = 1;
        $str = new TString($this->sql);
        while (($questionIndex = $str->indexOf("?", $startFrom)) != -1) {
            $value = $this->pars->get($n);
            $before = $str->substring(0, $questionIndex);
            $after = $str->substring($questionIndex + 1);
            $str = $before; $str->concat($value); $startFrom = $str->length();
            $str->concat($after);
            $n++;
        }
        $this->query = $str;
        return true;
    }

    // Set parameter value
    private function setValue($n, $val)
    {
        if ($n >= SIZE($this->pars))
            $this->pars->add($val);
        else
            $this->pars->set($n, $val);
    }

    /**
     * Set Integer parameter.
     * @param Integer $n Parameter number.
     * @param Integer $val Parameter value.
     */
    public function setInt($n, $val)
    {
        self::setValue($n, CAT($val));
    }

    /**
     * Set TString parameter.
     * @param Integer $n Parameter number.
     * @param TString $val Parameter value.
     */
    public function setString($n, $val)
    {
        if ($val instanceof TString) $val = $val->getValue();
        self::setValue($n, CAT("'", Strings::addSlashes($val), "'"));
    }

    /**
     * Set DateTime parameter.
     * @param Integer $n Parameter number.
     * @param TString $val Parameter value.
     */
    public function setDate($n, $val)
    {
        if ($val instanceof TString) $val = $val->getValue();
        self::setValue($n, CAT("'", DateTimes::format(DBConfig::SQL_DTS, DateTimes::getTime($val)), "'"));
    }

    /**
     * Set Float parameter.
     * @param Integer $n Parameter number.
     * @param Double $val Parameter value.
     */
    public function setFloat($n, $val)
    {
        self::setValue($n, CAT($val));
    }

    /**
     * Close.
     */
    public function close()
    {
        $this->link = null;
    }

    /**
     * Set DB link.
     * @param Object $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * Set SQL-query,
     * @param TString $sql
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }
}
