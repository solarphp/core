<?php

/**
* 
* Default template system for Solar; extended from Savant3.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Template
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Super.php 76 2005-03-26 21:08:37Z pmjones $
* 
*/

/**
* This class is extended from Savant3.
* @link http://phpsavant.com/
*/
include_once 'Template/Savant3.php';

/**
* 
* Default template system for Solar; extended from Savant3.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Template
* 
*/

class Solar_Template extends Savant3 {
	
	
	/**
	*
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		// get the user config from the Solar.config.php file, if any.
		$class = get_class($this);
		$default = Solar::config($class, null, array());
		
		// ... then merge the passed user config ...
		settype($config, 'array');
		$config = array_merge($default, $config);
		
		// ... and pass to the Savant3 constructor.
		parent::__construct($config);
	}
	
	
	/**
	*
	* Returns a Solar_Error object.
	* 
	* @access public
	* 
	* @param string $code A Savant3 'ERR_*' string.
	* 
	* @param array $info An array of error-specific information.
	* 
	* @return Solar_Error
	* 
	*/
	
	public function &error($code, $info = array(), $trace = true)
	{
		$class = get_class($this);
		$text = Solar::locale($class, $code); // will this work?
		settype($info, 'array');
		$level = E_USER_ERROR; // all errors are showstoppers
		$err = Solar::error($class, $code, $text, $info, $level, $trace);
		return $err;
	}
	
	
	/**
	*
	* Tests if an object is an error.
	* 
	* @access public
	* 
	* @param object &$obj The object to be tested.
	* 
	* @return boolean True if $obj is a Solar_Error.
	*
	*/
	
	public function isError(&$obj)
	{
		return Solar::isError($obj);
	}
}

?>