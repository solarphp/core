<?php

/**
* 
* No role source; always returns an empty array.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* No role source; always returns an empty array.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_User_Role_None extends Solar_Base {
	
	
	/**
	* 
	* Fetch the roles.
	*
	* @param string $user Username to get roles for.
	* 
	* @return mixed An array of discovered roles, or a Solar_Error object
	* if there was a file error.
	* 
	*/
	
	public function fetch($user)
	{
		return array();
	}
}
?>