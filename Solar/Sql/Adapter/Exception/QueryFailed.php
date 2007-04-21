<?php
/**
 * 
 * Exception: query failed for some reason.
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
 * 
 * Exception: query failed for some reason.
 * 
 * Generally thrown in place of a PDOException; it serves the same purpose,
 * but adds some more info about the failure.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Exception_QueryFailed extends Solar_Sql_Adapter_Exception {}
