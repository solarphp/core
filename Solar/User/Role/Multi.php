<?php

/**
* 
* Get roles from multiple sources and return as a single list.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id: None.php 113 2005-03-28 17:54:33Z pmjones $
* 
*/

/**
* 
* Get roles from multiple sources and return as a single list.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_User_Role_Multi extends Solar_Base {
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys are:
	* 
	* drivers => (array) The array of driver classes and optional configs.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'drivers' => array(
			'Solar_User_Role_None'
		)
	);
	
	/**
	* 
	* An array of the multiple driver instances.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $driver = array();
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	function __construct($config = null)
	{
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
			
			// add the driver instance
			$this->driver[] = Solar::object($class, $opts);
		}
	}
	
	
	/**
	* 
	* Fetch the roles from each of the drivers.
	*
	* @param string $user Username to get roles for.
	* 
	* @return mixed An array of discovered roles.
	* 
	*/
	
	function fetch($user)
	{
		// the list of all roles
		$list = array();
		
		// loop through all the drivers and collect roles
		foreach ($this->driver as $obj) {
		
			// fetch the role list
			$result = $obj->fetch($username);
			
			// let errors go silently from here
			if (! Solar::isError($result) && $result !== false) {
				// merge the results into the common list
				$list = array_merge(
					$list,
					(array) $result
				);
			}
		}
		
		return $list;
	}
}
?>