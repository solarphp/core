<?php

/**
* 
* Authenticate against an IMAP or POP3 mail server.
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
* Authenticate against an IMAP or POP3 mail server.
*
* @category Solar
* 
* @pacakge Solar
* 
*/

class Solar_User_Auth_Mail extends Solar_Base {
	
	
	/**
	* 
	* User-supplied configuration values.
	* 
	* Keys are:
	* 
	* mailbox => (string) An imap_open() mailbox string, e.g. "mail.example.com:143/imap"
	* or "mail.example.com:110/pop3".
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'mailbox' => null,
	);
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	function __construct($config = null)
	{
		
		// make sure the IMAP extension is available
		if (! extension_loaded('imap')) {
			return $this->error(
				'ERR_EXTENSION',
				array(),
				E_USER_ERROR
			);
		}
		
		// continue construction
		parent::__construct($config);
	}


	/**
	* 
	* Validate a username and password.
	* 
	* @param string $username Username to authenticate.
	* 
	* @param string $password The password to use.
	* 
	* @return boolean|Solar_Error True on success, false on failure,
	* or a Solar_Error object if there was a storage error.
	* 
	* @todo Check the server status with fsockopen().
	* 
	*/
	
	function valid($username, $password)
	{
		$mailbox = '{' . $this->config['mailbox'] . '}';
		$conn = @imap_open($mailbox, $username, $password, OP_HALFOPEN);
		if (is_resource($conn)) {
			@imap_close($conn);
			return true;
		} else {
			return false;
		}
	}
}
?>