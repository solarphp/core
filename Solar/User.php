<?php

/**
* 
* Meta-container for the current user to hold auth and roles.
* 
* When prefs and permissions come along, will hold those too.
* 
* @category Solar
* 
* @package Solar_User
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
* @todo Write convenience interface methods to get user, role, pref,
* and perm values from objects, even if those objects don't exist.
* This will save the developer from having to check if the object
* exists and then try to retrieve data (at least one less layer of
* complexity).  Alternatively, write a "None" object for each that
* has only blank methods; that will help avoid weird logic in this
* class.
* 
*/

/**
* 
* Meta-container for the current user to hold auth and roles.
* 
* When prefs and permissions come along, will hold those too.
* 
* @category Solar
* 
* @package Solar_User
* 
*/

class Solar_User extends Solar_Base
{
	/**
	* 
	* User-provided configuration values.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'auth' => null,
		'role' => null,
		'pref' => null,
		'perm' => null
	);
	
	
	/**
	* 
	* User authentication object.
	* 
	* @access public
	* 
	* @var object
	* 
	*/
	
	public $auth;
	
	
	/**
	* 
	* User roles (group membership) object.
	* 
	* @access public
	* 
	* @var object
	* 
	*/
	
	public $role;
	
	
	/**
	* 
	* User preferences object.
	* 
	* @access public
	* 
	* @var object
	* 
	*/
	
	public $pref;
	
	
	/**
	* 
	* User permissions object.
	* 
	* @access public
	* 
	* @var object
	* 
	*/
	
	public $perm;
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		// construction
		parent::__construct($config);
		
		// always set up an authentication object.
		$opts = null;
		// is the driver an array of custom configs?
		if (is_array($this->config['auth'])) {
			// yes, use the custom configs
			$opts = $this->config['auth'];
		}
		// instantiate the auth object
		$this->auth = Solar::object('Solar_User_Auth', $opts);
		
		// is there a configuration for a roles object?
		if (! empty($this->config['role'])) {
			// set up the roles object.
			// is the driver an array of custom configs?
			$opts = null;
			if (is_array($this->config['role'])) {
				// yes, use the custom configs
				$opts = $this->config['role'];
			}
			// instantiate the role object
			$this->role = Solar::object('Solar_User_Role', $opts);
		}
	}
	
	
	/**
	* 
	* Solar hooks.
	* 
	*/
	
	public function __solar($hook)
	{
		switch ($hook) {
		
		case 'start':
			// start up authentication
			$this->auth->start();
			
			// is this a valid authenticated user?
			if ($this->auth->statusCode == 'VALID') {
				// user is not valid
				if (is_object($this->role)) {
					// loop through role objects and load roles
					$this->role->fetch($this->auth->username);
				}
			} else {
				// user is not valid
				if (is_object($this->role)) {
					// clear out any roles
					$this->role->reset();
				}
			}
			
			
			break;
			
		case 'stop':
			break;
		}
	}
}
?>