<?php

/**
* 
* Provides an interface to PEAR_ErrorStack class for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Error_stack.php,v 1.2 2005/03/07 14:52:24 pmjones Exp $
* 
*/

/**
* The PEAR_ErrorStack class.
*/
require_once 'PEAR/ErrorStack.php';

/**
* 
* Provides an interface to PEAR_ErrorStack class for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Error_stack extends Savant3_Error {
	
	/**
	* 
	* Pushes an error onto the PEAR_ErrorStack.
	* 
	* @return void
	* 
	*/
	
	public function error()
	{
		// push an error onto the stack
		PEAR_ErrorStack::staticPush(
			'Savant3',    // package name
			$this->code,  // error code
			null,         // error level
			$this->info,  // user info
			$this->text   // error message
		);
	}
}
?>