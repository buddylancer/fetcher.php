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

use Bula\Model\Connection;
use Bula\Model\DOBase;

require_once("Bula/Model/Connection.php");
require_once("Bula/Model/DOBase.php");

/**
 * Manipulating with rules.
 */
class DOMapping extends DOBase
{
    /** Public constructor (overrides base constructor) */
    public function __construct($connection)
    {
        parent::__construct($connection);
        $this->tableName = "mappings";
        $this->idField = "i_MappingId";
    }
}
