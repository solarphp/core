<?php

/**
* 
* No role source; always returns an empty array.
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id: File.php 8 2005-02-14 22:44:41Z pmjones $
* 
*/

/**
* 
* No role source; always returns an empty array.
* 
* The file format is "username:group,group,group\n".  Example:
* 
* <code>
* pmjones:sysadmin
* boshag:writer
* agtsmith:staff,writer,editor,approver
* </code>
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
* 
*/

class Solar_User_Role_File extends Solar_Base {
	
	
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
	
	function fetch($user)
	{
		return array();
	}
}
?>