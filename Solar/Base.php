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
	* @param string $num If 1, returns a singular string; otherwise, returns
	* a plural string (if one exists).
	* 
	* @return string The locale string, or the original $key if no
	* string found.
	* 
	*/
	
	public function locale($key, $num = 1)
	{
		// is a locale directory specified?
		if (empty($this->config['locale'])) {
			// use the generic Solar locale strings
			return Solar::$locale->string('Solar', $key, $num);
		}
		
		// get a translation for the current class
		$class = get_class($this);
		$string = Solar::$locale->string($class, $key, $num);
		
		// is the translation same as the key?  if not, we're done.
		if ($string != $key) {
			return $string;
		}
		
		// key and string were the same, which means there was no
		// available translation.  make sure we have a translation file
		// loaded, then return whatever we get afterwards.
		Solar::$locale->load($class, $this->config['locale']);
		return Solar::$locale->string($class, $key, $num);
	}
}
?>