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

use Bula\Model\PreparedStatement;
use Bula\Objects\TString;

require_once("Bula/Meta.php");
require_once("Bula/Objects/TString.php");
require_once("DataAccess.php");
require_once("PreparedStatement.php");

/**
 * Implement operations with connection to the database.
 */
class Connection
{
    private $link; // Mysql link object
    private $stmt; // Prepared statement to use with connection

    /**
     * Open connection to the database.
     * @param TString $host Host name.
     * @param Integer $port Port number.
     * @param TString $admin Admin name.
     * @param TString $password Admin password.
     * @param TString $db DB name.
     * @param TString $charset DB charset.
     * @return Integer Result of operation (1 - OK, -1 - error).
     */
    public function open($host, $port, $admin, $password, $db, $charset = null)
    {
        $this->link = DataAccess::connect($host, $admin, $password, $db, $port); //TODO PHP
        if ($this->link == null || $this->link == false) {
            DataAccess::callErrorDelegate("Can't open DB! Check whether it exists!");
            return -1;
        }
        if ($charset != null)
            DataAccess::nonQuery($this->link, CAT("set names ", $charset));
        return 1;
    }

    /**
     * Close connection to the database.
     */
    public function close()
    {
        DataAccess::close($this->link);
        $this->link = null;
        unset($this->link);
    }

    /**
     * Prepare statement.
     * @param TString $sql SQL-query.
     * @return Prepared statement.
     */
    public function prepareStatement($sql)
    {
        $this->stmt = new PreparedStatement();
        $this->stmt->setLink($this->link);
        $this->stmt->setSql($sql);
        return $this->stmt;
    }
}
