<?php

/**
* 
* Methods for fetching and scrubbing superglobal data.
* 
* @category Solar
* 
* @package Solar_Scrub
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Valid.php 32 2005-02-21 04:25:10Z pmjones $
* 
*/

/**
* 
* Methods for retrieving and scrubbing superglobal data.
* 
* @category Solar
* 
* @package Solar_Valid
* 
*/

class Solar_Super {
	
	
	/**
	* 
	* Default scrubber callbacks for various superglobal elements.
	* 
	* @todo Allow a config group to specify the scrubbers?
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
	
		// $_ENV keys
		'env' => array(),
		
		// $_GET keys
		'get' => array(
			array('Solar_Super', 'unquote'),
			'strip_tags',
		),
		
		// $_POST keys
		'post' => array(
			array('Solar_Super', 'unquote'),
		),
		
		// $_COOKIE keys
		'cookie' => array(
			array('Solar_Super', 'unquote'),
		),
		
		// $_SERVER keys
		'server' => array(
			array('Solar_Super', 'unquote'),
			'strip_tags',
		),
		
		// $_FILES keys
		'files' => array(
			array('Solar_Super', 'unquote'),
		),
	);
	
	public function fetch($type, $key = null, $default = null)
	{
		// determine the callback scrubber set
		$scrub = $this->config[strtolower($type)];
		
		// convert 'type' to '_TYPE'; e.g., 'get' to '_GET'
		$type = strtoupper("_$type");
		
		// get the whole superglobal, or just one key?
		if (is_null($key) && isset($GLOBALS[$type])) {
		
			// no key selected, return the whole array
			return Solar::scrub($GLOBALS[$type], $scrub);
			
		} elseif (isset($GLOBALS[$type][$key])) {
		
			// looking for a specific key
			return Solar::scrub($GLOBALS[$type][$key], $scrub);
			
		} else {
		
			// specified key does not exist
			return $default;
			
		}
	}
	
	public static function unquote($var)
	{
		// discover if magic quotes are turned on
		static $quotes;
		if (! isset($quotes)) {
			$quotes = get_magic_quotes_gpc();
		}
		
		// if magic quotes are turned on, unquote the value.
		if ($quotes) {
			$var = stripslashes($var);
		}
		
		// done!
		return $var;
	}
	
}
?>