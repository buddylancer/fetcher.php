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

/**
 * Set info for database connection here.
 */
class DBConfig
{
    /** Database host */
    const DB_HOST = "localhost";
    /** Database name */
    const DB_NAME = "dbusnews";
    /** Database administrator name (if null - DB_NAME will be used) */
    const DB_ADMIN = null;
    /** Database password  (if null - DB_NAME will be used) */
    const DB_PASSWORD = null;
    /** Database character set */
    const DB_CHARSET = "utf8";
    /** Database port */
    const DB_PORT = 3306;
    /** Date/time format used for DB operations */
    const SQL_DTS = "Y-m-d H:i:s";
}
