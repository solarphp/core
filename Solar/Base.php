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
	* locale => The directory where locale string files for this class
	* are located.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'locale' => null,
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config Is merged with the default $config property array
	* and any values from the Solar.config.php file.
	* 
	*/
	
	public function __construct($config = null)
	{
		// allow construction-time config loading from arbitrary files
		if (is_string($config)) {
			$config = Solar::run($config);
		}
		
		// Solar.config.php values override class defaults
		$class = get_class($this);
		$solar = Solar::config($class, null, array());
		$this->config = array_merge($this->config, $solar);
		
		// construction-time values override Solar.config.php
		$this->config = array_merge($this->config, (array) $config);
		
		// auto-define the locale directory if needed
		if (empty($this->config['locale'])) {
			// converts "Solar_Example_Class" to
			// "Solar/Example/Class/Locale/"
			$this->config['locale'] = str_replace('_', '/', $class);
			$this->config['locale'] .= '/Locale/';
		}
		
		// cannot forcibly load the locale strings at this point,
		// something to do with Solar::$shared->locale not being ready.
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
	* Convenience method for pushing onto an existing Solar_Error stack.
	* 
	* @access protected
	* 
	* @param object $err An existing Solar_Error object.
	* 
	* @param int $code The error code.
	* 
	* @param array $info An array of error-specific data.
	* 
	* @param int $level The error level constant, e.g. E_USER_NOTICE.
	* 
	* @param bool $trace Whether or not to generate a debug_backtrace().
	* 
	*/
	
	protected function errorPush($err, $code, $info = array(), $level = null,
		$trace = null)
	{
		// automatic value for the class name
		$class = get_class($this);
		
		// automatic locale string for the error text
		$text = $this->locale($code);
		
		// make sure info is an array
		settype($info, 'array');
		
		// push the error onto the stack
		$err->push($class, $code, $text, $info, $level, $trace);
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
	
	public function solar($hook)
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
	* Uses the locale strings in the directory noted by $config['locale'];
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
			return Solar::locale('Solar', $key, $num);
		}
		
		// get a translation for the current class
		$class = get_class($this);
		$string = Solar::locale($class, $key, $num);
		
		// is the translation same as the key?  if not, we're done.
		if ($string != $key) {
			return $string;
		}
		
		// key and string were the same, which means there was no
		// available translation.  make sure we have a translation file
		// loaded, and try again.
		Solar::shared('locale')->load($class, $this->config['locale']);
		$string = Solar::locale($class, $key, $num);
		
		// again, is the translation same as the key?  if not, we're done.
		if ($string != $key) {
			return $string;
		}
		
		// fall back to the global Solar locale strings.
		return Solar::locale('Solar', $key, $num);
	}
}
?>