<?php

/**
* 
* Provides an interface to Solar::error() method for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Error_solar.php,v 1.2 2005/03/07 14:53:01 pmjones Exp $
* 
*/

/**
* The Solar class, which loads up the Solar_Base and Error classes.
*/
require_once 'Solar.php';

/**
* 
* Provides an interface to Solar::error() method for Savant.
*
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Error_solar extends Savant3_Error {
	
	/**
	* 
	* Extended behavior for Solar_Error.
	*
	* @access public
	*
	* @return void
	*
	*/
	
	public function error()
	{
		// throw a Solar error
		Solar::error(
			'Savant3',
			$this->code,
			$this->text,
			$this->info,
			E_USER_ERROR,
			true
		);
	}
}
?>