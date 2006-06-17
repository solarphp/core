<?php
/**
 * 
 * Exception: file does not exist or is not readable.
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
 * Solar_Base generic exception.
 */
Solar::loadClass('Solar_Base_Exception');

/**
 * 
 * Exception: file does not exist or is not readable.
 * 
 * @category Solar
 * 
 * @package Solar_Exception
 * 
 */
class Solar_Base_Exception_FileNotReadable extends Solar_Base_Exception {}
?>