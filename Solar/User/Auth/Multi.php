<?php

/**
* 
* Authenticate against multiple sources, falling back as needed.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id: Multi.php 144 2005-04-07 20:37:43Z pmjones $
* 
*/

/**
* 
* Authenticate against multiple sources, falling back as needed.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_User_Auth_Multi extends Solar_Base {
	
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
			'Solar_User_Auth_None'
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
		// basic construction
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
			
			// add the driver instance
			$this->driver[] = Solar::object($class, $opts);
		}
	}
	
	
	/**
	* 
	* Check is user is valid in any of the given drivers.
	* 
	* @param string $user Username to get roles for.
	* 
	* @return mixed An array of discovered roles.
	* 
	*/
	
	function valid($user)
	{
		foreach ($this->driver as $driver) {
			if ($driver->valid($user)) {
				return true;
			}
		}
		return false;
	}
}
?>