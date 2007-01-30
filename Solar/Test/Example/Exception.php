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
 * @version $Id: Exception.php 1315 2006-06-18 01:57:01Z pmjones $
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