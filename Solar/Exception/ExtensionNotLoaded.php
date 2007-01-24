<?php
/**
 * 
 * Exception: a required PHP extension is not loaded.
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
 * Exception: a required PHP extension is not loaded.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Exception_ExtensionNotLoaded extends Solar_Exception {}
