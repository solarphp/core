<?php
/**
 * 
 * Exception: file cannot be found.
 * 
 * @category Solar
 * 
 * @package Solar_Base
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
 * Exception: file cannot be found.
 * 
 * @category Solar
 * 
 * @package Solar_Base
 * 
 */
class Solar_Exception_FileNotFound extends Solar_Exception {}
