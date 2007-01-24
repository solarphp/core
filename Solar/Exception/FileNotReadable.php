<?php
/**
 * 
 * Exception: file does not exist or is not readable.
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
 * Exception: file does not exist or is not readable.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Exception_FileNotReadable extends Solar_Exception {}
