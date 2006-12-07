<?php
/**
 * 
 * Exception class.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Exception.php 1552 2006-07-27 22:01:46Z pmjones $
 * 
 */

/**
 * Base exception.
 */
Solar::loadClass('Solar_Sql_Exception');

/**
 * 
 * Exception class.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Exception extends Solar_Sql_Exception {}
