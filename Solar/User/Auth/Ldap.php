<?php

/**
* 
* Authenticate against an LDAP server.
*
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Auth
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id: Ldap.php,v 1.14 2005/02/08 01:42:27 pmjones Exp $
* 
*/

/**
* 
* Authenticate against an LDAP server.
*
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Auth
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
	
	public $config = array(
		'url'    => null,
		'format' => null,
	);
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	function __construct($config = null)
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