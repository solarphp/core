<?php
/**
 * 
 * Exception: a class or object method is not callable.
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
 * Exception: a class or object method is not callable.
 * 
 * @category Solar
 * 
 * @package Solar_Base
 * 
 */
class Solar_Exception_MethodNotCallable extends Solar_Exception {}
