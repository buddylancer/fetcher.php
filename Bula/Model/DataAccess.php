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

require_once("Bula\Meta.php");

/**
 * Facade class for interfacing with mysql database.
 */
class DataAccess {
    private static $error_delegate = "STOP";
    private static $print_delegate = null; // Set "PR" for debug, set null for release

    /**
     * Connect to the database.
     * @param TString $host Host name.
     * @param TString $admin Admin name.
     * @param TString $password Admin password.
     * @param TString $db Database name.
     * @param TString $port Port number.
     * @return Object Link to the database.
     */
    public static function connect($host, $admin, $password, $db, $port) {
        return mysqli_connect($host, $admin, $password, $db, $port);
    }

    /**
     * Close the connection to the database.
     * @param Object $link Link to the database.
     */
    public static function close($link) {
        mysqli_close($link);
    }

    /**
     * Execute query.
     * @param Object $link Link to the database.
     * @param RString $input SQL-query to execute.
     * @return Object Result of query execution.
     */
    public static function selectQuery($link, $input) {
        return mysqli_query($link, $input);
    }
    public static function updateQuery($link, $input) {
        return mysqli_query($link, $input);
    }
    public static function nonQuery($link, $input) {
        return mysqli_query($link, $input);
    }

    /**
     * Get number of rows affected by last query.
     * @param Object $link Link to the database.
     * @return Integer
     */
    public static function affectedRows($link) {
        return mysqli_affected_rows($link);
    }

    /**
     * Get unique ID for last inserted record.
     * @param Object $link Link to the database.
     * @return Integer
     */
    public static function insertId($link) {
        return mysqli_insert_id($link);
    }

    /**
     * Get number of resulting rows for last query.
     * @return Object Result of query execution.
     * @return Integer
     */
    public static function numRows($result) {
        return mysqli_num_rows($result);
    }

    /**
     * Get next row (as Hashtable) for last query.
     * @return Object Result of query execution.
     * @return Hashtable Next row or null.
     */
    public static function fetchArray($result) {
        return mysqli_fetch_array($result);
    }

    /**
     * Free last query result.
     * @return Object Result of query execution.
     */
    public static function freeResult($result) {
        mysqli_free_result($result);
    }

    /**
     * Set function for error printing.
     * @param Object $delegateFunction Function delegate.
     */
    public static function setErrorDelegate($delegateFunction) {
        self::$error_delegate = $delegateFunction;
    }

    /**
     * Set function for debug printing.
     * @param Object $delegateFunction Function delegate.
     */
    public static function setPrintDelegate($delegateFunction) {
        self::$print_delegate = $delegateFunction;
    }

    /**
     * Call delegate function for error printing.
     * @param TString $input Error message.
     */
    public static function callErrorDelegate($input) {
        if (self::$error_delegate != null)
            call_user_func_array(self::$error_delegate, array($input));
    }

    /**
     * Call delegate function for debug printing.
     * @param TString $input Debug message.
     */
    public static function callPrintDelegate($input) {
        if (self::$print_delegate != null)
            call_user_func_array(self::$print_delegate, array($input));
    }
}
