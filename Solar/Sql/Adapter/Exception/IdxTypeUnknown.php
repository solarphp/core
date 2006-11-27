<?php
/**
 * 
 * Exception: index type is unknown (should be 'normal' or 'unique').
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
 * Exception: index type is unknown (should be 'normal' or 'unique').
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Exception_IdxTypeUnknown extends Solar_Sql_Adapter_Exception {}
?>