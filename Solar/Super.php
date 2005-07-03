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

class Solar_Super extends Solar_Base {
	
	
	/**
	* 
	* User-defined configuration values.
	* 
	* These are the default scrubber callbacks for various superglobal types.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
	
		// $_COOKIE scrubbers
		'cookie' => array(
			array('Solar_Super', 'magicStripSlashes'),
			'strip_tags',
		),
		
		// $_ENV scrubbers
		'env' => array(),
		
		// $_FILES scrubbers
		'files' => array(
			array('Solar_Super', 'magicStripSlashes'),
		),
		
		// $_GET scrubbers
		'get' => array(
			array('Solar_Super', 'magicStripSlashes'),
			'strip_tags'
		),
		
		// $_POST scrubbers
		'post' => array(
			array('Solar_Super', 'magicStripSlashes'),
		),
		
		// $_SERVER scrubbers
		'server' => array(
			array('Solar_Super', 'magicStripSlashes'),
			'strip_tags'
		),
		
		// $_SESSION scrubbers
		'session' => array(),
	);
	
	
	/**
	* 
	* Only allow access to these superglobals.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $allowed = array(
		'cookie',
		'env',
		'files', 
		'get',
		'post',
		'server',
		'session',
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
	* @param string $type The superglobal variable name to fetch from;
	* e.g., 'server' for $_SERVER or 'get' for $_GET.
	* 
	* @param string $key The superglobal array key to retrieve; if null,
	* will return the entire superglobal array for that type.
	* 
	* @param mixed $default If the requested superglobal array key does
	* not exist, return this value instead.
	* 
	* @return mixed The value of the superglobal type array key, or the
	* default value if the key did not exist.
	* 
	*/
	
	public function fetch($type, $key = null, $default = null)
	{
		// force $type to lowercase for access checking
		$type = strtolower($type);
		
		// disallow access to non-superglobals
		if (! in_array($type, $this->allowed)) {
			return $default;
		}
		
		// determine the callback scrubber set
		$callbacks = $this->config[$type];
		
		// force 'type' to '_TYPE' (e.g., 'get' to '_GET')
		// so we can access it properly through $GLOBALS
		$type = strtoupper("_$type");
		
		// get the whole superglobal, or just one key?
		if (is_null($key) && isset($GLOBALS[$type])) {
		
			// no key selected, return the whole array
			return $this->scrub($GLOBALS[$type], $callbacks);
			
		} elseif (isset($GLOBALS[$type][$key])) {
		
			// looking for a specific key
			return $this->scrub($GLOBALS[$type][$key], $callbacks);
			
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
	* @param mixed $value Strips slashes from this value if
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
	
	
	/**
	* 
	* Recursively applies scrubber callbacks to a value.
	* 
	* @access protected
	* 
	* @param mixed $value The value to scrub.
	* 
	* @param array $callbacks The scrubber callbacks to apply.
	* 
	* @return mixed The scrubbed value.
	* 
	*/
	
	protected function scrub($value, $callbacks = null)
	{
		settype($callbacks, 'array');
		if (is_array($value)) {
			foreach ($callbacks as $func) {
				array_walk_recursive($value, $func);
			}
		} else {
			foreach ($callbacks as $func) {
				call_user_func($func, $value);
			}
		}
		return $value;
	}
}
?>