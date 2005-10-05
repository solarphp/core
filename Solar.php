<?php

/**
* 
* Solar: Simple Object Library and Application repository for PHP5.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Define where the Solar.config.php file is located.
*/
if (! defined('SOLAR_CONFIG_PATH')) {
	define('SOLAR_CONFIG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Solar.config.php');
}

/**
* The base for all Solar classes (except Solar itself ;-).
*/
require_once 'Solar/Base.php';

/**
* The Solar_Error class, needed for Solar::isError().
*/
require_once 'Solar/Error.php';


/**
* 
* Encapsulates shared configuration, objects, and methods for Solar apps.
* 
* @category Solar
* 
* @package Solar
* 
* @version @package_version@
* 
*/

class Solar {
	
	
	/**
	* 
	* The values read in from the configuration file.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public static $config = array();
	
	
	/**
	* 
	* Shared singleton objects are properties of the $shared object.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected static $shared = null;
	
	
	/**
	* 
	* Status flag (whether Solar has started or not).
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected static $status = false;
	
	
	/**
	* 
	* Start Solar: get config, load shared objects, run start scripts.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public static function start($alt_config = null)
	{
		// don't re-start if we're already running.
		if (Solar::$status) {
			return;
		}
		
		// initialize $shared property as a StdClass object
		Solar::$shared = new StdClass;
		
		// set up the standard Solar environment
		Solar::environment();
		
		// load the config file values. note that we use $config here,
		// not config(), because we are setting the value of the static
		// property.  use alternate config source if one is given.
		if (is_array($alt_config)) {
			Solar::$config = $alt_config;
		} elseif (is_object($alt_config)) {
			Solar::$config = (array) $alt_config;
		} elseif (is_string($alt_config)) {
			Solar::$config = (array) Solar::run($alt_config);
		} else {
			Solar::$config = (array) Solar::run(SOLAR_CONFIG_PATH);
		}
		
		// process ini settings from config file
		$settings = Solar::config('Solar', 'ini_set', array());
		foreach ($settings as $key => $val) {
			ini_set($key, $val);
		}
		
		// make sure the baseline set of shared objects is in place,
		// ready to be called when needed.
		$baseline = array(
			'sql'      => 'Solar_Sql',
			'user'     => 'Solar_User',
			'locale'   => 'Solar_Locale',
			'template' => 'Solar_Template',
			'content'  => 'Solar_Content',
		);
		
		foreach ($baseline as $name => $class) {
			if (! isset(Solar::$config['Solar']['shared'][$name])) {
				Solar::$config['Solar']['shared'][$name] = $class;
			}
		}
		
		// build the shared locale object
		Solar::shared('locale');
		
		// load the autoshare objects ...
		$list = Solar::config('Solar', 'autoshare', array());
		
		// make sure 'user' is there somewhere (by default, at the top).
		// we do this so that its solar('stop') method gets called.
		if (! in_array('user', $list)) {
			array_unshift($list, 'user');
		}
		
		// loop through each autoshare object and load it ...
		foreach ($list as $name) {
			Solar::shared($name);
		}
		
		// ... and then run each of the solar('start') methods.
		// (we load and run in separate loops because some 
		// objects may depend on others).
		foreach ($list as $name) {
			// is_callable() doesn't seem to work with an 
			// object instance, but it works fine with just
			// the class name.  so we'll use that.
			$class = get_class(Solar::$shared->$name);
			if (is_callable($class, 'solar')) {
				Solar::$shared->$name->solar('start');
			}
		}
		
		// finally, run any 'start' hook scripts
		foreach (Solar::config('Solar', 'start', array()) as $file) {
			Solar::run($file);
		}
		
		// and we're done!
		Solar::$status = true;
	}
	
	
	/**
	* 
	* Stop Solar: run stop scripts and shared object "stop" hooks.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public static function stop()
	{
		// run the application-defined stop scripts.
		foreach (Solar::config('Solar', 'stop', array()) as $file) {
			Solar::run($file);
		}
		
		// find the solar('stop') hook in each auto-shared
		// object ...
		$list = Solar::config('Solar', 'autoshare', array());
		
		// ... and run them in reverse order.
		$list = array_reverse($list);
		foreach ($list as $name) {
			// is_callable() doesn't seem to work with an 
			// object instance, but it works fine with just
			// the class name.  so we'll use that.
			$class = get_class(Solar::$shared->$name);
			if (is_callable($class, 'solar')) {
				Solar::$shared->$name->solar('stop');
			}
		}
		
		// reset the status flag, and we're done.
		Solar::$status = false;
	}
	
	
	/**
	* 
	* Returns the API version for Solar.
	* 
	* @access public
	* 
	* @return string A PHP-standard version number.
	* 
	*/
	
	public static function apiVersion()
	{
		return '@package_version@';
	}
	
	
	/**
	* 
	* Gets translated locale string for a class and key.
	* 
	* @access public
	* 
	* @param string $class The class of the translation.
	* 
	* @param string $key The translation key.
	* 
	* @param mixed $num Helps determine whether to get a singular
	* or plural translation.
	* 
	* @return string A translated locale string.
	* 
	*/
	
	public static function locale($class, $key, $num = 1)
	{
		return Solar::shared('locale')->string($class, $key, $num);
	}
	
	
	/**
	* 
	* A "sham" method; __autoload() does not work with static calls.
	* 
	* If the class name exists as a key in $config['Solar']['registry'],
	* that array element value will be used as the file path.  If not,
	* the class name will be turned into a file path by converting
	* all instances of '_' in the class name to DIRECTORY_SEPARATOR
	* (i.e., '/' on Unix and '\' on Windows).
	* 
	* @access public
	* 
	* @param string $class A Solar (or other) class name.
	* 
	* @return void
	* 
	* @todo Add localization for errors
	* 
	* @todo Replace this with global __autoload() function?  Would mean
	* dropping the 'registry' capability, but I don't think that's a 
	* big deal.  Might get in the way of other systems using __autoload()
	* though, when Solar is combined with other systems.
	* 
	*/
	
	public static function loadClass($class)
	{
		// pre-empt searching for the class
		if (class_exists($class)) {
			return;
		}
		
		if (trim($class) == '') {
			return Solar::error(
				'Solar', // class
				'ERR_LOADCLASS_EMPTY', // code
				'No class named for loading', // text
				array('class' => $class), // info
				E_USER_ERROR // level
			);
		}
		
		// is there a registry, and if so,
		// is the class explicitly registered?
		if (isset(Solar::$config['Solar']['registry']) &&
			is_array(Solar::$config['Solar']['registry']) &&
			array_key_exists($class, Solar::$config['Solar']['registry'])) {
			
			// yes, use the registered file path
			$file = Solar::$config['Solar']['registry'][$class];
			
		} else {
		
			// no, auto-convert the class name to a file path.
			$file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
			
		}
		
		// include the file and check for failure.
		$result = Solar::run($file);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// if the class was not in the file, we have a problem.
		if (! class_exists($class)) {
			return Solar::error(
				'Solar', // class
				'ERR_LOADCLASS_EXIST', // code
				'Class does not exist in loaded file', // text
				array('class' => $class, 'file' => $file), // info
				E_USER_ERROR // level
			);
		}
	}
	
	
	/**
	* 
	* Runs a script in an isolated scope.
	* 
	* @access public
	* 
	* @param string A script path and file name.
	* 
	* @return mixed The final return of the included file, if any, or a
	* Solar_Error if the file could not be opened.
	* 
	*/
	
	public static function run($file)
	{
		// this hack is the equivalent of is_readable(), but it also
		// checks the include-path to see if the file exists.
		$fp = @fopen($file, 'r', true);
		$ok = ($fp) ? true : false;
		@fclose($fp);
		
		// could we find the file?
		if ($ok) {
			// clean up the local scope
			unset($file);
			unset($fp);
			unset($ok);
			// include the file and return its results
			return include(func_get_arg(0));
		} else {
			// could not open the file for reading
			return Solar::error(
				'Solar',
				'ERR_FILE_OPEN',
				'ERR_FILE_OPEN',
				array('file' => $file),
				E_USER_WARNING
			);
		}
	}
	
	
	/**
	* 
	* Convenience method to instantiate and configure a Solar object.
	* 
	* @access public
	* 
	* @param string $class The class name.
	* 
	* @param array $config The configuration array for the class.
	* 
	* @return object A new instance of the requested Solar class.
	* 
	*/
	
	public static function object($class, $config = null)
	{
		$result = Solar::loadClass($class);
		if (Solar::isError($result)) {
			return $result;
		} else {
			$obj = new $class($config);
			return $obj;
		}
	}
	
	
	/**
	* 
	* Convenience method to instantiate a shared (singleton) object.
	* 
	* @access public
	* 
	* @param string $class The class name.
	* 
	* @return object A singleton instance of the requested Solar class.
	* 
	* @todo Localize these errors.
	* 
	*/
	
	public static function shared($name)
	{
		// has the shared object already been loaded?
		if (! isset(Solar::$shared->$name)) {
			
			// not loaded yet.  can we find the associated info?
			if (isset(Solar::$config['Solar']['shared']) &&
				is_array(Solar::$config['Solar']['shared']) &&
				array_key_exists($name, Solar::$config['Solar']['shared'])) {
				
				// found the associated info; always convert to an array.
				$info = Solar::$config['Solar']['shared'][$name];
				settype($info, 'array');
				
				// get the class name
				$class = $info[0];
				
				// get the config, if it exists.
				$config = array_key_exists(1, $info) ? $info[1] : null;
				
				// instantiate.
				Solar::$shared->$name = Solar::object($class, $config);
				
			} else {
			
				// did not find the info.  that's an error.
				Solar::$shared->$name = Solar::error(
					'Solar',
					'ERR_SHARED_NAME',
					"shared object name $name not in config file under ['Solar']['shared']", 
					array('shared' => $name),
					E_USER_ERROR
				);
				
			}
		}
		
		// return the shared instance.
		return Solar::$shared->$name;
	}
	
	
	/**
	* 
	* Safely get a configuration group array or element value.
	* 
	* Returns a blank default value if the group or element is not set.
	* 
	* <code>
	* // get a group config array
	* $result = Solar::config('group')
	* 
	* // get a group config array, or an empty array if the
	* // group does not exist
	* $result = Solar::config('group', null, array())
	* 
	* // get an element of a group
	* $result = Solar::config('group', 'elem')
	* 
	* // get an element, or a blank string if the group and
	* // element does not exist
	* $result = Solar::config('group', 'elem', '')
	* </code>
	* 
	* @access public
	* 
	* @param string $group The name of the group.
	* 
	* @param string $elem The name of the element in the group.
	* 
	* @param mixed $default If the group or element is not set, return
	* this value instead.  If this is not set and group was requested,
	* returns an empty array; if not set and an element was requested,
	* returns null.
	* 
	* @return mixed The value of the configuration group or element.
	* 
	*/
	
	public static function config($group, $elem = null)
	{
		// was a default fallback value passed?  we do it this way
		// instead of defining a parameter because we need to return
		// a different default value depending on whether a group
		// was requested, or an element.
		if (func_num_args() > 2) {
			$default = func_get_arg(2);
		}
		
		// are we looking for a group or an element?
		if (is_null($elem)) {
			
			// looking for a group. if no default passed, set up an
			// empty array.
			if (! isset($default)) {
				$default = array();
			}
			
			// find the requested group.
			if (empty(Solar::$config[$group])) {
				return $default;
			} else {
				return Solar::$config[$group];
			}
			
		} else {
			
			// looking for an element. if no default passed, set up a
			// null.
			if (! isset($default)) {
				$default = null;
			}
			
			// find the requested group and element.
			if (empty(Solar::$config[$group][$elem])) {
				return $default;
			} else {
				return Solar::$config[$group][$elem];
			}
		}
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_GET array.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value.  Applies scrubbers automatically.
	* 
	* @access public
	* 
	* @param string $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function get($key = null, $default = null)
	{
		return Solar::super('_GET', $key, $default);
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_POST array.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value.  Applies scrubbers automatically.
	* 
	* @access public
	* 
	* @param string $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function post($key = null, $default = null)
	{
		return Solar::super('_POST', $key, $default);
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_COOKIE array.
	* 
	* @access public
	* 
	* @param string $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function cookie($key = null, $default = null)
	{
		return Solar::super('_COOKIE', $key, $default);
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_SERVER array.
	* 
	* @access public
	* 
	* @param string $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function server($key = null, $default = null)
	{
		return Solar::super('_SERVER', $key, $default);
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_SESSION array.
	* 
	* @access public
	* 
	* @param string $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function session($key = null, $default = null)
	{
		return Solar::super('_SESSION', $key, $default);
	}
	
	
	/**
	* 
	* Safely gets the value of $_SERVER['PATH_INFO'] element.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value.  Applies scrubbers automatically.
	* 
	* @access public
	* 
	* @param int $key The array element; if null, returns the whole
	* array.
	* 
	* @param mixed $default If the requested array element is
	* not set, return this value.
	* 
	* @return mixed The array element value (if set), or the
	* $default value (if not).
	* 
	*/
	
	public static function pathinfo($key = null, $default = null)
	{
		// get the pathinfo as passed
		$info = Solar::super('_SERVER', 'PATH_INFO', '');
		
		// explode into its elements
		$elem = explode('/', $info);
		
		// drop off the first element (it's always blank)
		array_shift($elem);
		
		// look for the requested element number
		if (is_null($key)) {
		
			// no key selected, return the whole $elem array
			return $elem;
			
		} elseif (isset($elem[$key])) {
		
			// looking for a specific element key
			return $elem[$key];
			
		} else {
		
			// specified element key does not exist
			return $default;
			
		}
	}
	
	
	/**
	* 
	* Simple error object generator.
	* 
	* @param string $class The class that generated the error.
	* 
	* @param mixed $code An scalar error code.
	* 
	* @param string $text Any error message text.
	* 
	* @param array $info Additional error information in an associative
	* array.
	* 
	* @param int $level The error level severity, generally E_USER_NOTICE,
	* E_USER_WARNING, or E_USER_ERROR.
	* 
	* @param bool $trace Whether or not to add a debug_backtrace().
	* 
	* @return object A Solar_Error object.
	* 
	*/
	
	public static function error($class, $code, $text = '', $info = array(), 
		$level = null, $trace = null)
	{
		$obj = Solar::object('Solar_Error');
		$obj->push($class, $code, $text, $info, $level, $trace);
		return $obj;
	}
	
	
	/**
	* 
	* Checks to see in an object is a Solar_Error or not.
	* 
	* @param object $obj Check this object to see if it is of, or is
	* descended from, the Solar_Error class.
	* 
	* @return bool True if an error object, false if not.
	* 
	*/
	
	public static function isError($obj)
	{
		// it has to at least be an object
		if (! is_object($obj)) {
			return false;
		}
		
		// see if it matches Solar_Error
		$is = $obj instanceof Solar_Error;
		$sub = is_subclass_of($obj, 'Solar_Error');
		return ($is || $sub);
	}
	
	
	/**
	* 
	* Simple variable dumper.
	* 
	* @access public
	* 
	* @param mixed &$var The variable to dump.
	* 
	* @param string $label A label for the dumped output.
	* 
	* @return void
	* 
	*/
	
	public static function dump(&$var, $label = null)
	{
		$obj = Solar::object('Solar_Debug_Var');
		echo $obj->dump($var, $label);
	}
	
	
	/**
	* 
	* "Fixes" a directory string.
	* 
	* Basically, use slashes anywhere you need a directory separator.
	* Then run the string through fixdir() and the slashes will be converted
	* to the proper separator (e.g. '\' on Windows).  Also, adds a trailing
	* separator to the string for you.
	* 
	* @param string $dir The directory string to 'fix'.
	* 
	* @return string The "fixed" directory.
	* 
	*/
	
	public static function fixdir($dir)
	{
		$sep = DIRECTORY_SEPARATOR;
		$dir = str_replace('/', $sep, $dir);
		if (substr($dir, -1) != $sep) {
			$dir .= $sep;
		}
		return $dir;
	}
	
	
	/**
	* 
	* Sets up the standard Solar environment (including some security).
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function environment()
	{
		// clear out registered globals?
		// (this code from Richard Heyes and Stefan Esser)
		if (ini_get('register_globals')) {
			
			/* // previous version
			// Variables that shouldn't be unset
			$keep = array('GLOBALS', '_GET', '_POST', '_COOKIE',
				'_REQUEST', '_SERVER', '_ENV', '_FILES');
			
			// Sources of global input
			$input = array_merge($_GET,	$_POST, $_COOKIE, $_SERVER,
				$_ENV, $_FILES, isset($_SESSION) ? $_SESSION : array());
			
			// Clear out sources of global input
			foreach ($input as $key => $val) {
				if (! in_array($key, $keep) &&
					array_key_exists($key, $GLOBALS)) {
					unset($GLOBALS[$key]);
				}
			}
			*/
			
			// Variables that shouldn't be unset
			$noUnset = array(
				'GLOBALS', '_GET', '_POST', '_COOKIE',
				'_REQUEST', '_SERVER', '_ENV', '_FILES'
			);
			
			// sources of global input.
			// 
			// the ternary check on $_SESSION is to make sure that
			// it's really an array, not just a string; if it's just a
			// string, that can bypass this check somehow.  Stefan
			// Esser knows how this works, but I don't.
			$input = array_merge($_GET, $_POST, $_COOKIE,
				$_SERVER, $_ENV, $_FILES,
				isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array()
			);
			
			// unset globals set from input sources, but don't unset
			// the sources themselves.
			foreach ($input as $k => $v) {
				if (! in_array($k, $noUnset) && isset($GLOBALS[$k])) {
					unset($GLOBALS[$k]);
				}
			}
		}
		
		// remove magic quotes if they are enabled; sybase quotes
		// override normal quotes.
		if (ini_get('magic_quotes_gpc')) {
			
			// what kind of quotes are we using?
			if (ini_get('magic_quotes_sybase')) {
				// sybase quotes
				$func = array('Solar', 'dispelSybase');
			} else {
				// "normal" slashed quotes
				$func = array('Solar', 'dispelQuotes');
			}
			
			// dispel magic quotes from superglobals
			array_walk_recursive($_GET, $func);
			array_walk_recursive($_POST, $func);
			array_walk_recursive($_COOKIE, $func);
			array_walk_recursive($_FILES, $func);
			array_walk_recursive($_SERVER, $func);
		}
		
		// make sure automatic quoting of values from, e.g., SQL sources
		// is turned off. turn off sybase quotes too.
		ini_set('magic_quotes_runtime', false);
		ini_set('magic_quotes_sybase',  false);
	}
	
	
	/**
	* 
	* A stripslashes() alias that supports array_walk_recursive().
	* 
	* @param string &$value The value to strip slashes from.
	* 
	* @return void
	* 
	*/
	
	protected static function dispelQuotes(&$value)
	{
		$value = stripslashes($value);
	}
	
	
	/**
	* 
	* A str_replace() for Sybase quotes; supports array_walk_recursive().
	* 
	* @param string &$value The value to dispel Sybase quotes from.
	* 
	* @return void
	* 
	*/
	
	protected static function dispelSybase(&$value)
	{
		$value = str_replace("''", "'", $value);
	}
	
	
	/**
	* 
	* Fetches a superglobal value by key, or a default value.
	* 
	* @access public
	* 
	* @param string $type The superglobal variable name to fetch from;
	* e.g., '_SERVER' for $_SERVER or '_GET' for $_GET.
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
	
	protected static function super($type, $key = null, $default = null)
	{
		// get the whole superglobal, or just one key?
		if (is_null($key) && isset($GLOBALS[$type])) {
		
			// no key selected, return the whole array
			return $GLOBALS[$type];
			
		} elseif (isset($GLOBALS[$type][$key])) {
		
			// looking for a specific key
			return $GLOBALS[$type][$key];
			
		} else {
		
			// specified key does not exist
			return $default;
			
		}
	}
}
?>