<?php

/**
* 
* Meta-container for the current user to hold auth and roles.
* 
* When prefs and permissions come along, will hold those too.
* 
* @category Solar
* 
* @package Solar
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
* Meta-container for the current user to hold auth and roles.
* 
* When prefs and permissions come along, will hold those too.
* 
* @category Solar
* 
* @package Solar
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
	* @access public
	* 
	* @param array $config User-provided configuration options.
	* 
	* @return void
	* 
	*/
	
	public function __construct($config = null)
	{
		// construction
		parent::__construct($config);
		
		// set up an authentication object.
		$this->auth = Solar::object('Solar_User_Auth', $this->config['auth']);
		
		// set up the roles object.
		$this->role = Solar::object('Solar_User_Role', $this->config['role']);
	}
	
	
	/**
	* 
	* Solar hooks.
	* 
	* @access public
	* 
	* @param string $hook The hook to execute (e.g., 'start' or 'stop').
	* 
	* @return void
	* 
	*/
	
	public function __solar($hook)
	{
		switch ($hook) {
		
		case 'start':
			// start up authentication
			$this->auth->start();
			
			// is this a valid authenticated user?
			if ($this->auth->status_code == 'VALID') {
				// yes, the user is authenticated as valid.
				// load up any roles for the user.
				$this->role->fetch($this->auth->username);
			} else {
				// no, user is not valid.  
				// clear out any previous roles.
				$this->role->reset();
			}
			
			
			break;
			
		case 'stop':
			break;
		}
	}
}
?>