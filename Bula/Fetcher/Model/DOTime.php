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

use Bula\Model\DOBase;

require_once("Bula/Model/DOBase.php");

/**
 * Manipulating with times.
 */
class DOTime extends DOBase {
    /** Public constructor (overrides base constructor) */
	public function __construct(){
		parent::__construct();
		$this->table_name = "as_of_time";
		$this->id_field = "i_Id";
	}
}
