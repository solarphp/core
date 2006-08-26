<?php
/**
 * 
 * Exception to note an incomplete test.
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
 * Parent class for test exceptions.
 */
Solar::loadClass('Solar_Test_Exception');

/**
 * 
 * Exception to note an incomplete test.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Exception_Todo extends Solar_Test_Exception {}
?>