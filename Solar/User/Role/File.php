<?php

/**
* 
* Get roles from a simple file.
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
* @version $Id$
* 
* @todo Convert to look through Unix-style group files, not the 
* custom version noted in the comments.
* 
*/

/**
* 
* Get roles from a simple file.
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
	
	function fetch($user)
	{
		
		// force the full, real path to the CVS file
		$file = realpath($this->config['file']);
		
		// does the file exist?
		if (! file_exists($file) || ! is_readable($file)) {
			return $this->error(
				'ERR_FILE_FIND',
				array('file' => $file),
				E_USER_ERROR
			);
		}
		
		// open the file
		$fp = @fopen($file, 'r');
		if (! $fp) {
			return $this->error(
				'ERR_FILE_OPEN',
				array('file' => $file),
				E_USER_ERROR
			);
		}
		
		// find the user's line in the file
		$len = strlen($user) + 1;
		$ok = false;
		while ($line = fgets($fp)) {
			if (substr($line, 0, $len) == "$user:") {
				// found the line, leave the loop
				$ok = true;
				break;
			}
		}
		
		// close the file
		fclose($fp);
		
		// did we find the username?
		if (! $ok) {
			// username not in the file
			return false;
		}
		
		// get the text after the "$user:" part.
		// $len was set when finding the user's line in the file.
		$tmp = substr($line, $len);
		
		// break up the line into pieces, then capture the comma-separate
		// group names into an array.
		$tmp = explode(',', trim($tmp));
		$list = array();
		foreach ($tmp as $key => $val) {
			// no empty groups allowed
			$val = trim($val);
			if ($val) {
				$list[] = $val;
			}
		}
		
		// done!
		return $list;
	}
}
?>