<?php

/**
* 
* Authenticate against an LDAP server.
*
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_User
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Authenticate against an LDAP server.
*
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_User
* 
*/

class Solar_User_Auth_Ldap extends Solar_Base {
	
	
	/**
	* 
	* User-supplied configuration values.
	* 
	* Keys are:
	* 
	* url => (string) URL to the LDAP server, e.g. "ldaps://example.com:389".
	* 
	* format => (string) Sprintf() format string for the LDAP query; %s
	* represents the username.  Example: "uid=%s,dc=example,dc=com".
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'url'    => null,
		'format' => null,
	);
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		// make sure we have LDAP available
		if (! extension_loaded('ldap')) {
			return $this->error(
				'ERR_EXTENSION',
				array('extension' => 'ldap'),
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
	* @param string $user Username to authenticate.
	* 
	* @param string $pass The plain-text password to use.
	* 
	* @return boolean|Solar_Error True on success, false on failure,
	* or a Solar_Error object if there was a connection error.
	* 
	*/
	
	public function valid($username, $password)
	{
		// connect
		$conn = @ldap_connect($this->config['url']);
		
		// did the connection work?
		if (! $conn) {
			return $this->error(
				'ERR_CONNECT',
				array($this->config),
				E_USER_ERROR
			);
		}
		
		// upgrade to LDAP3 when possible
		@ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		// bind to the server
		$rdn = sprintf($this->config['format'], $username);
		$bind = @ldap_bind($conn, $rdn, $password);
		ldap_close($conn);
		
		// return the bind-value
		if (! $bind) {
			// not using $this->error() because we need fine control
			// over the error text
			return Solar::error(
				get_class($this), // class name
				@ldap_errno($conn), // error number
				@ldap_error($conn), // error text
				array($this->config), // other info
				E_USER_NOTICE // error level
			);
		} else {
			return true;
		}
	}
}

?>