<?php

/**
* 
* Authenticate against htpasswd or CVS pserver file.
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
* Authenticate against htpasswd or CVS pserver file.
* 
* Format for each line is "username:hashedpassword\n";
*
* @category Solar
* 
* @pacakge Solar
* 
*/

class Solar_User_Auth_Htpasswd extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys:
	* 
	* file => (string) Path to password file.
	* 
	* salt => (string) A salt prefix to make cracking passwords harder.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'file' => null,
		'salt' => null,
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
		
		// break up the pieces: 0 = username, 1 = crypted password. may
		// be more than that but we don't care.
		$tmp = explode(':', trim($line));
		$real = $tmp[1];
		
		// check if the password hashes match.
		return ($real == crypt($this->config['salt'] . $pass, $real));
	}
}
?>