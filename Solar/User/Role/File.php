<?php

/**
* 
* Get roles from a Unix-style groups file.
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
* @todo rename to Unix, add Ini file handler as well
* 
*/

/**
* 
* Get roles from a Unix-style groups file.
* 
* The file format is "group:user1,user2,user3\n".  Example:
* 
* <code>
* sysadmin:pmjones
* writer:pmjones,boshag,agtsmith
* editor:pmjones,agtsmith
* </code>
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_User_Role_File extends Solar_Base {
	
	
	/**
	* 
	* User-supplied configuration values.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'file' => null
	);
	
		
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
		// force the full, real path to the file
		$file = realpath($this->config['file']);
		
		// does the file exist?
		if (! file_exists($file) || ! is_readable($file)) {
			return $this->error(
				'ERR_FILE_FIND',
				array('file' => $file),
				E_USER_ERROR
			);
		}
		
		// load the file as an array of lines
		$lines = file($file);
		
		// the list of roles
		$list = array();
		
		// loop through each line, find the group, then see if the user
		// is on the line anywhere
		foreach ($lines as $line) {
		
			// break apart at first ':'
			$pos = strpos(':', $line);
			
			// the group name is the part before the ':'
			$group = substr($line, 0, $pos);
			
			// the list of users comes after
			$tmp = substr($line, $pos+1);
			$users = explode(',', $tmp);
			
			// is the user part of the group?
			if (in_array($user, $users)) {
				$list[] = $group;
			}
		}
		
		// done!
		return $list;
	}
}
?>