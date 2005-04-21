<?php

/**
* 
* Provides an interface to PEAR_Error class for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Error_pear.php,v 1.2 2005/03/07 14:54:23 pmjones Exp $
* 
*/

/**
* The PEAR_Error class.
*/
require_once 'PEAR.php';

/**
* 
* Provides an interface to PEAR_Error class for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Error_pear extends Savant3_Error {
	
	/**
	* 
	* Extended behavior for PEAR_Error.
	*
	* @access public
	*
	* @return void
	*
	*/
	
	public function error()
	{
		// throw a PEAR_Error
		PEAR::throwError($this->text, $this->code, $this->info);
	}
}
?>