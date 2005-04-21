<?php

include_once 'Template/Savant3.php';

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
	* @return boolean True if $obj is an error object of the type
	* Savant3_Error, or is a subclass that Savant3_Error. False if not.
	*
	*/
	
	public function isError(&$obj)
	{
		return Solar::isError($obj);
	}
}

?>