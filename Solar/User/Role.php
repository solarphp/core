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
* @version $Id: Role.php,v 1.5 2005/02/08 01:42:27 pmjones Exp $
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
		'drivers' => array()
	);
	
	// ----------------------------------------------------------------
	// 
	// Public properties.
	// 
	// ----------------------------------------------------------------
	
	
	/**
	* 
	* An array of driver object instances.
	* 
	* @access private
	* 
	* @var array
	* 
	*/
	
	protected $driver = array();
	
	
	// ----------------------------------------------------------------
	// 
	// Public methods.
	// 
	// ----------------------------------------------------------------
	
	
	/**
	* 
	* Constructor to set up the storage driver.
	*
	* @access public
	* 
	* @param array $conf The config options.
	* 
	* @return object
	* 
	*/
	
	public function __construct($conf = null)
	{
		// basic config option settings
		parent::__construct($conf);
		
		// instantiate the driver objects
		foreach ($this->config['drivers'] as $key => $info) {
			
			// is the driver an array (custom configs)
			// or a string (default configs)?
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
		if (! array_key_exists('Solar_User_Role', $_SESSION)) {
			$_SESSION['Solar_User_Role'] = null;
		}
		
		// keep a reference to the session array
		$this->list =& $_SESSION['Solar_User_Role'];

		// does the session array already exist?
		// if so, and if we're not forcing refreshes,
		// the we don't need to do anything.
		if (is_array($this->list) && ! $this->config['refresh']) {
			return $this->list;
		}
		
		// loop through the role objects and add their results to the list
		$this->list = array();
		foreach ($this->driver as $obj) {
		
			// fetch the role list
			$result = $obj->fetch($username);
			
			// let errors go silently from here
			if (! Solar::isError($result) && $result !== false) {
				// merge the results into the common list
				settype($result, 'array');
				$this->list = array_merge($this->list, $result);
			}
		}
		
		// return the results
		return $this->list;
	}
	
	
	/**
	* 
	* Resets the role list to nothing.
	* 
	*/
	
	public function reset()
	{
		$this->list = null;
	}
	
	
	/**
	* 
	* Check to see if a user is in a role.
	* 
	*/
	
	public function in($role = null)
	{
		return in_array($role, $this->list);
	}
	
	
	/**
	* 
	* Check to see if a user is in any of the listed roles.
	* 
	*/
	
	public function inAny()
	{
		if (is_array(func_get_arg(0))) {
			$roles = func_get_arg(0);
		} else {
			$roles = func_get_args();
		}
		
		foreach ($roles as $role) {
			if (in_array($role, $this->list)) {
				return true;
			}
		}
		return false;
	}
	
	
	/**
	* 
	* Check to see if a user is in all of the listed roles.
	* 
	*/
	
	public function inAll($roles = null)
	{
		if (is_array(func_get_arg(0))) {
			$roles = func_get_arg(0);
		} else {
			$roles = func_get_args();
		}
		
		foreach ($roles as $role) {
			if (! in_array($role, $this->list)) {
				return false;
			}
		}
		return true;
	}
}
?>