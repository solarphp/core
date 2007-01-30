<?php
/**
 * 
 * Exception: file does not exist or is not readable.
 * 
 * @category Solar
 * 
 * @package Solar_Exception
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FileNotReadable.php 1310 2006-06-17 23:29:57Z pmjones $
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
 * @package Solar_Exception
 * 
 */
class Solar_Exception_FileNotReadable extends Solar_Exception {}
?>