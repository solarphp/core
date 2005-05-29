<?php

/**
* 
* Methods for fetching scrubbed superglobal data.
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
* Methods for fetching scrubbed superglobal data.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Super {
	
	
	/**
	* 
	* User-defined configuration values.
	* 
	* These are the default scrubber callbacks for various superglobal types.
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
			array('Solar_Super', 'magicStripslashes'),
			'strip_tags',
		),
		
		// $_POST keys
		'post' => array(
			array('Solar_Super', 'magicStripslashes'),
		),
		
		// $_COOKIE keys
		'cookie' => array(
			array('Solar_Super', 'magicStripslashes'),
		),
		
		// $_SERVER keys
		'server' => array(
			array('Solar_Super', 'magicStripslashes'),
			'strip_tags',
		),
		
		// $_SESSION keys
		'session' => array(),
		
		// $_FILES keys
		'files' => array(
			array('Solar_Super', 'magicStripslashes'),
		),
	);
	
	
	/**
	* 
	* Fetches a superglobal value by key, or a default value.
	* 
	* Automatically and recursively applies the scrubber callbacks to
	* the value.
	* 
	* @access public
	* 
	* @param $type string The superglobal variable name to fetch from;
	* e.g., 'server' for $_SERVER or 'get' for $_GET.
	* 
	* @param $key string The superglobal array key to retrieve; if null,
	* will return the entire superglobal array for that type.
	* 
	* @param $default mixed If the requested superglobal array key does
	* not exist, return this value instead.
	* 
	* @return mixed The value of the superglobal type array key, or the
	* default value if the key did not exist.
	* 
	*/
	
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
	
	
	/**
	* 
	* Strips slashes from a value, but only if magic_quotes_gpc is turned on.
	* 
	* @access public
	* 
	* @param $value mixed Strips slashes from this value if
	* magic_quotes_gpc is turned on; does nothing if magic_quotes_gpc is
	* off.
	* 
	* @return mixed The value after stripslashes().
	* 
	*/
	
	public static function magicStripslashes($value)
	{
		// discover if magic quotes are turned on
		static $quotes;
		if (! isset($quotes)) {
			$quotes = get_magic_quotes_gpc();
		}
		
		// if magic quotes are turned on, unquote the value.
		if ($quotes) {
			$value = stripslashes($value);
		}
		
		// done!
		return $value;
	}
	
}
?>