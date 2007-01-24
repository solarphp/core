<?php
/**
 * 
 * Exception: connection to a resource failed.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Generic Solar_Exception.
 */
Solar::loadClass('Solar_Exception');

/**
 * 
 * Exception: connection to a resource failed.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Exception_ConnectionFailed extends Solar_Exception {}
