<?php

/**
* 
* Abstract base class for all Solar objects.
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
* Abstract base class for all Solar objects.
* 
* @category Solar
* 
* @package Solar
* 
*/

abstract class Solar_Base {
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys are:
	* 
	* locale => The directory where locale string files live.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array();
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config Is merged with the default $config property array
	* and any defaults from Solar.config.php.
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
		
		// ... and merge with the class defaults.
		$this->config = array_merge($this->config, $config);
		
		// forcibly load locale strings.
		$this->locale();
	}
	
	
	/**
	* 
	* Reports the API version for this class.
	* 
	* If you don't override this method, your classes will use the same
	* API version string as the Solar package itself.
	* 
	* @access public
	* 
	* @return string A PHP-standard version number.
	* 
	*/
	
	public function apiVersion()
	{
		return '@package_version@';
	}
	
	
	/**
	* 
	* Convenience method for returning Solar::error() with localized text.
	* 
	* @access protected
	* 
	* @param int $code The error code.
	* 
	* @param array $info An array of error-specific data.
	* 
	* @param int $level The error level constant, e.g. E_USER_NOTICE.
	* 
	* @param bool $trace Whether or not to generate a debug_backtrace().
	* 
	* @return object A Solar_Error object.
	* 
	*/
	
	protected function error($code, $info = array(), $level = null,
		$trace = null)
	{
		// automatic value for the class name
		$class = get_class($this);
		
		// automatic locale string for the error text
		$text = $this->locale($code);
		
		// make sure info is an array
		settype($info, 'array');
		
		// generate the object and return
		$err = Solar::error($class, $code, $text, $info, $level, $trace);
		return $err;
	}
	
	
	/**
	* 
	* Provides hooks for Solar::start() and Solar::stop() on shared objects.
	* 
	* @access public
	* 
	* @param string $hook The hook to activate, typically 'start' or 'stop'.
	* 
	* @return void
	* 
	*/
	
	public function __solar($hook)
	{
		switch ($hook) {
		case 'start':
			// do nothing
			break;
		case 'stop':
			// do nothing
			break;
		}
	}
	
	
	/**
	* 
	* Looks up locale strings based on a key.
	* 
	* Uses the locale strings in the directory noted by $conf['locale'];
	* if no such key exists, falls back to the strings for the parent
	* class, and finally falls back to the Solar/Locale strings.
	* 
	* @access public
	* 
	* @param string $key The key to get a locale string for.
	* 
	* @return string The locale string, or the original $key if no
	* string found.
	* 
	*/
	
	public function locale($key = null)
	{
		// is a locale directory specified?
		if (empty($this->config['locale'])) {
			// use the generic Solar locale strings
			return Solar::locale('Solar', $key);
		}
		
		// otherwise, use the class-specific strings.
		// find the current class.
		$class = get_class($this);
		
		// load the strings if needed
		if (! Solar::locale($class)) {
			
			// create the file name
			$dir = Solar::fixdir($this->config['locale']);
			$file = $dir . Solar::localeCode() . '.php';
			
			// load and set the strings
			$strings = (array) Solar::run($file);
			Solar::locale($class, null, (array) $strings);
		}
		
		// try to read the string
		$string = Solar::locale($class, $key);
		
		// if it's the same as the key, there was no string ...
		if ($string == $key) {
			// ... so try the parent class.  this is kind of weak,
			// becuase if the parent hasn't loaded strings, it will
			// still fail.
			$class = get_parent_class($this);
			$string = Solar::locale($class, $key);
		}
		
		// return whatever we have at this point.
		return $string;
	}
}
?>