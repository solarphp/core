<?php
/**
 * 
 * Exception: column name is too long or too short.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ColNameLength.php 1552 2006-07-27 22:01:46Z pmjones $
 * 
 */

/**
 * Base SQL exception.
 */
Solar::loadClass('Solar_Sql_Exception');

/**
 * 
 * Exception: column name is too long or too short.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Exception_ColNameLength extends Solar_Sql_Exception {}
?>