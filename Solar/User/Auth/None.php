<?php

/**
* 
* Authenticate against nothing; defaults all authentication to "failed."
* 
* @category Solar
* 
* @pacakge Solar
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
* Authenticate against nothing; defaults all authentication to "failed."
* 
* @category Solar
* 
* @pacakge Solar
* 
*/

class Solar_User_Auth_None extends Solar_Base {
	
	
	/**
	* 
	* Validate a username and password.  Always fails.
	*
	* @param string $user Username to authenticate.
	* 
	* @param string $pass The plain-text password to use.
	* 
	* @return boolean True on success, false on failure.
	* 
	*/
	
	function valid($user, $pass)
	{
		return false;
	}
}
?>