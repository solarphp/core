<?php

/**
* 
* Throws PHP5 exceptions for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Error_exception.php,v 1.4 2005/03/07 15:31:04 pmjones Exp $
* 
*/


/**
* 
* A simple Savant3_Exception class.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Exception extends Exception {
}


/**
* 
* Throws PHP5 exceptions for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Error_exception extends Savant3_Error {
	
	/**
	* 
	* Throws a Savant3_Exception in PHP5.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function error()
	{
		$message = $this->code . ' (' . $this->text . ')';
		throw new Savant3_Exception($message);
	}
}
?>