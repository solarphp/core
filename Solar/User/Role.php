<?php

/**
* 
* Class for reading user roles (groups) from multiple sources.
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
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
* Class for reading user roles (groups) from multiple sources.
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
* 
*/

class Solar_User_Role extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys are:
	* 
	* refresh => (bool) Whether or not to refresh the groups on every load.
	* 
	* drivers => (array) The array of driver classes and optional configs.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'refresh' => false,
		'drivers' => array('Solar_User_Role_None')
	);
	
	
	/**
	* 
	* An array of driver object instances.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $driver = array();
	
	
	/**
	* 
	* A convenient reference to $_SESSION['Solar_User_Role'].
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $list = null;
	
	
	/**
	* 
	* Constructor to set up the storage driver.
	*
	* @access public
	* 
	* @param array $config The config options.
	* 
	* @return object
	* 
	*/
	
	public function __construct($config = null)
	{
		// basic config option settings
		parent::__construct($config);
		
		// make sure the drivers config is an array
		settype($this->config['drivers'], 'array');
		
		// instantiate the driver objects
		foreach ($this->config['drivers'] as $key => $info) {
			
			// is the driver value an array (for custom configs)
			// or a string (for default configs)?
			if (is_array($info)) {
				$class = $info[0];
				$opts = $info[1];
			} else {
				$class = $info;
				$opts = null;
			}
			
			$this->driver[] = Solar::object($class, $opts);
		}
		
	}
	
	
	/**
	* 
	* Refresh the list of roles for the given user.
	* 
	* @access public
	* 
	* @return array The list of roles for the authenticated user.
	* 
	*/
	
	public function fetch($username)
	{
		// make sure we have a session value
		if (! isset('Solar_User_Role', $_SESSION)) {
			$_SESSION['Solar_User_Role'] = null;
		}
		
		// does the session array already exist?
		// if so, and if we're not forcing refreshes,
		// the we don't need to do anything.
		if (is_array($_SESSION['Solar_User_Role']) &&
			! $this->config['refresh']) {
			return $_SESSION['Solar_User_Role'];
		}
		
		// reset the roles list
		$_SESSION['Solar_User_Role'] = array();
		
		// loop through all the drivers and collect roles
		foreach ($this->driver as $obj) {
		
			// fetch the role list
			$result = $obj->fetch($username);
			
			// let errors go silently from here
			if (! Solar::isError($result) && $result !== false) {
				// merge the results into the common list
				$_SESSION['Solar_User_Role'] = array_merge(
					$_SESSION['Solar_User_Role'],
					(array) $result
				);
			}
		}
		
		// return the results
		return $_SESSION['Solar_User_Role'];
	}
	
	
	/**
	* 
	* Resets the role list to nothing.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function reset()
	{
		$_SESSION['Solar_User_Role'] = null;
	}
	
	
	/**
	* 
	* Check to see if a user is in a role.
	* 
	* @access public
	* 
	* @param string $role The role to check.
	* 
	* @return void
	* 
	*/
	
	public function in($role = null)
	{
		return in_array($role, $_SESSION['Solar_User_Role']);
	}
	
	
	/**
	* 
	* Check to see if a user is in any of the listed roles.
	* 
	* @access public
	* 
	* @param string|array $roles The role(s) to check.
	* 
	* @return boolean True if the user is in any of the listed roles (a
	* logical 'or'), false if not.
	* 
	*/
	
	public function inAny($roles = array())
	{
		// loop through all of the roles, returning 'true' the first
		// time we find a matching role.
		foreach ((array) $roles as $role) {
			if (in_array($role, $_SESSION['Solar_User_Role'])) {
				return true;
			}
		}
		
		// we got through the whole array without finding a match.
		// therefore, user was not in any of the roles.
		return false;
	}
	
	
	/**
	* 
	* Check to see if a user is in all of the listed roles.
	* 
	* @access public
	* 
	* @param string|array $roles The role(s) to check.
	* 
	* @return boolean True if the user is in all of the listed roles (a
	* logical 'and'), false if not.
	* 
	*/
	
	public function inAll($roles = array())
	{
		// loop through all of the roles, returning 'false' the first
		// time we find the user is not in one of the roles.
		foreach ((array) $roles as $role) {
			if (! in_array($role, $_SESSION['Solar_User_Role'])) {
				return false;
			}
		}
		
		// we got through the whole list; therefore, the user is in all
		// of the noted roles.
		return true;
	}
}
?>