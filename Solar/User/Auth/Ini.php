<?php

/**
* 
* Authenticate against .ini style files (not very secure).
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
* Authenticate against .ini style files.
* 
* Format for each line is "username = plainpassword\n";
*
* @category Solar
* 
* @package Solar
* 
*/

class Solar_User_Auth_Ini extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys:
	* 
	* file => (string) Path to password file.
	* 
	* group => (string) The group in which usernames reside.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'file' => null,
		'group' => 'users',
	);


	/**
	* 
	* Validate a username and password.
	*
	* @param string $user Username to authenticate.
	* 
	* @param string $pass The plain-text password to use.
	* 
	* @return boolean|Solar_Error True on success, false on failure,
	* or a Solar_Error object if there was a file error.
	* 
	*/
	
	function valid($user, $pass)
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
		
		// it's an ini-style file
		$data = parse_ini_file($file, true);
		
		// find the [users] list
		$list = (array) $data[$this->config['group'];
		
		// by default the user is not valid
		$valid = false;
		
		// there must be an entry for the username,
		// and the plain-text password must match.
		if (! empty($list[$user]) && $list[$user] = $pass) {
			$valid = true;
		}
		return $valid;
	}
}
?>