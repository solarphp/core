<?php
/**
 * 
 * Exception: table name is a reserved word.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Base SQL exception.
 */
Solar::loadClass('Solar_Sql_Adapter_Exception');

/**
 * 
 * Exception: table name is a reserved word.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Exception_TableNameReserved extends Solar_Sql_Adapter_Exception {}
?>