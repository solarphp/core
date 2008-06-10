<?php
/**
 * 
 * Class for connecting to SQLite (version 2) databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sqlite.php 3210 2008-06-09 23:50:06Z pmjones $
 * 
 */
class Solar_Sql_Adapter_Sqlite2 extends Solar_Sql_Adapter_Sqlite
{
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'sqlite2';
}