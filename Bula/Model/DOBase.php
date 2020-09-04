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
use Bula\Objects\Enumerator;
use Bula\Objects\Hashtable;
use Bula\Objects\TString;
use Bula\Objects\Strings;

require_once("Bula/Meta.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Enumerator.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");

require_once("DBConfig.php");
require_once("RecordSet.php");
require_once("Connection.php");
require_once("DataSet.php");

/**
 * Base class for manipulating with DB objects.
 */
class DOBase {
    private $db_connection = null;

    /**
     * Name of a DB table.
     * @var TString
     */
    protected $table_name;

    /**
     * Name of a table ID field.
     * @var TString
     */
    protected $id_field;

    /** Public constructor */
    public function __construct() {
        if (DBConfig::$Connection == null)
            DBConfig::$Connection = $this->createConnection();

        $this->db_connection = DBConfig::$Connection;
    }

    // Create connection to the database given parameters from DBConfig.
    private function createConnection() {
        $oConn = new Connection();
        $db_admin = DBConfig::DB_ADMIN != null ? DBConfig::DB_ADMIN : DBConfig::DB_NAME;
        $db_password = DBConfig::DB_PASSWORD != null ? DBConfig::DB_PASSWORD : DBConfig::DB_NAME;
        $ret = 0;
        if (DBConfig::DB_CHARSET != null)
            $ret = $oConn->open(DBConfig::DB_HOST, DBConfig::DB_PORT, $db_admin, $db_password, DBConfig::DB_NAME, DBConfig::DB_CHARSET);
        else
            $ret = $oConn->open(DBConfig::DB_HOST, DBConfig::DB_PORT, $db_admin, $db_password, DBConfig::DB_NAME);
        if ($ret == -1)
            $oConn = null;
        return $oConn;
    }

    /**
     * Get current connection.
     * @return Connection Current connection.
     */
    public function getConnection() {
        return $this->db_connection;
    }

    /**
     * Get current ID field name.
     * @return TString
     */
    public function getIdField() {
        return $this->id_field;
    }

    /**
     * Get DataSet based on query and parameters (all records).
     * @param TString $query SQL-query to execute.
     * @param Object[] $pars Query parameters.
     * @return DataSet Resulting data set.
     */
    public function getDataSet($query, $pars) {
        $oStmt = $this->db_connection->prepareStatement($query);
        if ($pars != null && SIZE($pars) > 0) {
            $n = 1;
            for ($i = 0; $i < SIZE($pars); $i += 2) {
                $type = /*(TString)*/$pars[$i];
                $value = $pars[$i+1];
                CALL($oStmt, $type, ARR($n, $value));
                $n++;
            }
        }
        $oRs = $oStmt->executeQuery();
        if ($oRs == null) {
            $oStmt->close();
            return null;
        }

        $ds = new DataSet();
        while ($oRs->next() != 0) {
            $ds->addRow($oRs->record);
        }
        $oRs->close();
        $oStmt->close();
        return $ds;
    }

    /**
     * Get DataSet based on query and parameters (only records of the list with rows length).
     * @param TString $query SQL-query to execute.
     * @param Object[] $pars Query parameters.
     * @param Integer $list List number.
     * @param Integer $rows Number of rows in a list.
     * @return DataSet Resulting data set.
     */
    public function getDataSetList($query, $pars, $list, $rows) {
        if ($rows <= 0 || $list <= 0)
            return $this->getDataSet($query, $pars);

        $oStmt = $this->db_connection->prepareStatement($query);
        if (SIZE($pars) > 0) {
            $n = 1;
            for ($p = 0; $p < SIZE($pars); $p += 2) {
                $type = $pars[$p];
                $value = $pars[$p+1];
                CALL($oStmt, $type, ARR($n, $value));
                $n++;
            }
        }
        $oRs = $oStmt->executeQuery();
        if ($oRs == null)
            return null;

        $ds = new DataSet();
        $total_rows = $oRs->getRows();
        $ds->setTotalPages(INT(($total_rows - 1) / $rows + 1));

        $count = 0;
        if ($list != 1) {
            $count = ($list - 1) * $rows;
            while ($oRs->next() != 0) {
                $count--;
                if ($count == 0)
                    break;
            }
        }

        $count = 0;
        while ($oRs->next() != 0) {
            if ($count == $rows)
                break;
            $ds->addRow($oRs->record);
            //$ds->setSize($ds->getSize() + 1);
            $count++;
        }

        $oRs->close();
        $oStmt->close();
        return $ds;
    }

    /**
     * Update database using $query and $parameters
     * @param TString $query SQL-query to execute.
     * @param Object[] $pars Query parameters.
     * @param TString $operation Operation - "update" (default) or "insert".
     * @return Integer Update status (or inserted ID for "insert" operation).
     */
    protected function updateInternal($query, $pars, $operation= "update") {
        $oStmt = $this->db_connection->prepareStatement($query);
        if (SIZE($pars) > 0) {
            $n = 1;
            for ($i = 0; $i < SIZE($pars); $i += 2) {
                $type = /*(TString)*/$pars[$i];
                $value = $pars[$i+1];
                CALL($oStmt, $type, ARR($n, $value));
                $n++;
            }
        }
        $ret = $oStmt->executeUpdate();
        if ($ret > 0 && EQ($operation, "insert"))
            $ret = $oStmt->getInsertId();
        $oStmt->close();
        return $ret;
    }

    /**
     * Get DataSet based on record ID.
     * @param Integer $id Unique ID.
     * @return DataSet Resulting data set.
     */
    public function getById($id) {
        $query = Strings::concat(
            " select * from ", $this->table_name,
            " where ", $this->id_field, " = ?"
        );
        $pars = array("setInt", $id);
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get DataSet containing IDs only.
     * @param TString $where Where condition [optional].
     * @param TString $order Field to order by [optional].
     * @return DataSet Resulting data set.
     */
    public function enumIds($where= null, $order= null) {
        $query = Strings::concat(
            " select ", $this->id_field, " from ", $this->table_name, " _this ",
            (BLANK($where) ? null : CAT(" where ", $where)),
            " order by ",
            (BLANK($order) ? $this->id_field : $order)
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get DataSet with all records enumerated.
     * @param TString $where Where condition [optional].
     * @param TString $order Field to order by [optional].
     * @return DataSet Resulting data set.
     */
    public function enumAll($where= null, $order= null) {
        $query = Strings::concat(
            " select * from ", $this->table_name, " _this ",
            (BLANK($where) ? null : CAT(" where ", $where)),
            (BLANK($order) ? null : CAT(" order by ", $order))
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get DataSet containing only required fields.
     * @param TString $fields Fields to include (divided by ',').
     * @param TString $where Where condition [optional].
     * @param TString $order Field to order by [optional].
     * @return DataSet Resulting data set.
     */
    public function enumFields($fields, $where= null, $order= null) {
        $query = Strings::concat(
            " select ", $fields, " from ", $this->table_name, " _this ",
            (BLANK($where) ? null : CAT(" where ", $where)),
            (BLANK($order) ? null : CAT(" order by ", $order))
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get DataSet containing only required fields or all fields [default].
     * @param TString $fields Fields to include (divided by ',').
     * @param TString $where Where condition [optional].
     * @param TString $order Field to order by [optional].
     * @return DataSet Resulting data set.
     */
    public function select($fields= null, $where= null, $order= null) {
        if ($fields == null)
            $fields = "_this.*";

        $query = Strings::concat(
            " select ", $fields,
            " from ", $this->table_name, " _this ",
            (BLANK($where) ? null : CAT(" where ", $where)),
            (BLANK($order) ? null : CAT(" order by ", $order))
        );
        $pars = array();
        return $this->getDataSet($query, $pars);
    }

    /**
     * Get DataSet containing only the given list of rows (with required fields or all fields).
     * @param Integer $list List number.
     * @param Integer $rows Number of rows in a list.
     * @param TString $fields Fields to include (divided by ',').
     * @param TString $where Where condition [optional].
     * @param TString $order Field to order by [optional].
     * @return DataSet Resulting data set.
     */
    public function selectList($list, $rows, $fields= null, $where= null, $order= null) {
        if ($fields == null)
            $fields = "_this.*";
        $query = Strings::concat(
            " select ",  $fields,
            " from ", $this->table_name, " _this ",
            (BLANK($where) ? null : CAT(" where ", $where)),
            (BLANK($order) ? null : CAT(" order by ", $order))
        );

        $pars = array();
        $ds = $this->getDataSetList($query, $pars, $list, $rows);
        return $ds;
    }

    /**
     * Delete record by ID.
     * @param Integer $id Unique ID.
     * @return Integer Result of operation.
     */
    public function deleteById($id) {
        $query = Strings::concat(
            " delete from ", $this->table_name,
            " where ", $this->id_field, " = ?"
        );
        $pars = array("setInt", $id);
        return $this->updateInternal($query, $pars, "update");
    }

    /**
     * Insert new record based on given fields.
     * @param Hashtable $fields The set of fields.
     * @return Integer Result of SQL-query execution.
     */
    public function insert(Hashtable $fields) {
        $keys = $fields->keys();
        $field_names = new TString();
        $field_values = new TString();
        $pars = array();
        //$pars->setPullValues(true);
        $n = 0;
        while ($keys->moveNext()) {
            $key = /*(TString)*/$keys->current();
            if ($n != 0) $field_names->concat(", ");
            if ($n != 0) $field_values->concat(", ");
            $field_names->concat($key);
            $field_values->concat("?");
            $pars = ADD($pars, $this->setFunction($key), $fields->get($key));
            $n++;
        }
        $query = Strings::concat(
            " insert into ", $this->table_name, " (", $field_names, ") ",
            " values (", $field_values, ")"
        );
        return $this->updateInternal($query, $pars, "insert");
    }

    /**
     * Update existing record by ID based on given fields.
     * @param Integer $id Unique record ID.
     * @param Hashtable $fields The set of fields.
     * @return Integer Result of SQL-query execution.
     */
    public function updateById($id, Hashtable $fields) {
        $keys = $fields->keys();
        $set_values = new TString();
        $pars = array();
        //$pars->setPullValues(true);
        $n = 0;
        while ($keys->moveNext()) {
            $key = /*(TString)*/$keys->current();
            if ($key == $this->id_field) //TODO PHP
                continue;
            if ($n != 0)
                $set_values->concat(", ");
            $set_values->concat(CAT($key, " = ?"));
            $pars = ADD($pars, $this->setFunction($key), $fields->get($key));
            $n++;
        }
        $pars = ADD($pars, $this->setFunction($this->id_field), $id);
        $query = Strings::concat(
            " update ", $this->table_name, " set ", $set_values,
            " where (", $this->id_field, " = ?)"
        );
        return $this->updateInternal($query, $pars, "update");
    }

    /**
     * Map function for setting parameters.
     * @param TString $key  Field name.
     * @return TString Function name for setting that field.
     */
    private function setFunction($key) {
        if (!$key instanceof TString) $key = new TString($key);
        $prefix = $key->substring(0, 2);
        $func = "setString";
        if ($prefix->equals("s_") || $prefix->equals("t_"))
            $func = "setString";
        else if ($prefix->equals("i_") || $prefix->equals("b_"))
            $func = "setInt";
        else if ($prefix->equals("f_"))
            $func = "setFloat";
        else if ($prefix->equals("d_"))
            $func = "setDate";
        return $func;
    }
}
