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
 * @version $Id: Todo.php 1186 2006-05-21 15:38:37Z pmjones $
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