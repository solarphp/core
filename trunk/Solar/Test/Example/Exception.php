<?php
/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Parent exception class.
 */
Solar::loadClass('Solar_Test_Exception');

/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Example_Exception extends Solar_Test_Exception {}
?>