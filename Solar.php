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
* Where the Solar.config.php file is located.
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
* @todo Add a cookie() getter.
* 
*/

class Solar {
	
	
	/**
	* 
	* The values read in from the configuration file.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected static $config = array();
	
	
	/**
	* 
	* Locale strings.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected static $locale = array();
	
	
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
	* Start the Solar: get config, load shared objects, run start scripts.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public static function start()
	{
		// don't re-start if we're already running.
		if (Solar::$status) {
			return;
		}
		
		// make sure $shared is a StdClass object
		Solar::$shared = new StdClass;
		
		// by default, show all the errors. you can reduce this later in
		// the config file.
		ini_set('error_reporting', E_ALL|E_STRICT);
		ini_set('display_errors', true);
		
		// load the config file values. note that we use $config here, not
		// config(), because we are setting the value of the static
		// property.
		Solar::$config = Solar::run(SOLAR_CONFIG_PATH);
		
		// make sure we have a locale code
		if (! isset(Solar::$config['Solar']['locale_code'])) {
			Solar::$config['Solar']['locale_code'] = 'en_US';
		}
		
		// build the baseline locale strings
		Solar::localeCode(Solar::$config['Solar']['locale_code']);
		
		// process ini settings
		foreach (Solar::config('Solar', 'ini_set', array()) as $key => $val) {
			ini_set($key, $val);
		}
		
		// load all autoshare objects ...
		$list = Solar::config('Solar', 'autoshare', array());
		foreach ($list as $name) {
			Solar::shared($name);
		}
		
		// ... and then run their __solar('start') methods.
		// (we load and run in separate loops because some 
		// objects may depend on others).
		foreach ($list as $name) {
			// is_callable() doesn't seem to work with an 
			// object instance, but it works fine with just
			// the class name.  so we'll use that.
			$class = get_class(Solar::$shared->$name);
			if (is_callable($class, '__solar')) {
				Solar::$shared->$name->__solar('start');
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
	* Stop the Solar: run stop scripts and shared object "stop" hooks.
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
		
		// find the __solar('stop') hook in each auto-shared
		// object ...
		$list = Solar::config('Solar', 'autoshare', array());
		
		// ... and run them in reverse order.
		$list = array_reverse($list);
		foreach ($list as $name) {
			// is_callable() doesn't seem to work with an 
			// object instance, but it works fine with just
			// the class name.  so we'll use that.
			$class = get_class(Solar::$shared->$name);
			if (is_callable($class, '__solar')) {
				Solar::$shared->$name->__solar('stop');
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
	* Gets/sets the locale code.
	* 
	* @access public
	* 
	* @param string $code Leave null to get the current locale code, or 
	* specify a code to set the locale.
	* 
	* @return string The current locale code.
	* 
	*/
	
	public static function localeCode($code = null)
	{
		if (! is_null($code)) {
			
			// find the baseline strings file and load it
			$file = Solar::fixdir('Solar/Locale/') . "$code.php";
			$strings = Solar::run($file);
			
			// were there strings loaded?
			if ($strings) {
				// yes, reset to the new code and save the strings
				Solar::$config['Solar']['locale_code'] = $code;
				Solar::$locale = array(
					'Solar' => $strings
				);
			} else {
				// no new strings
				return false;
			}
		}
		
		// the default action: return the current code.
		return Solar::$config['Solar']['locale_code'];
	}
	
	
	/**
	* 
	* Gets/sets locale strings for a class.
	* 
	* <code>
	* // get all locale strings by class as an assoc array
	* $array = Solar::locale('Class');
	* 
	* // get one locale string by class and key
	* $string = Solar::locale('Class', 'key');
	* 
	* // set all locale strings and keys for a class
	* Solar::locale('Class', null, $array);
	* 
	* // set one locale string for a class and key
	* Solar::locale('Class', 'key', 'string');
	* </code>
	* 
	* @access public
	* 
	* @return mixed A locale string, or void when setting values.
	* 
	*/
	
	public static function locale($class, $key = null, $val = null)
	{
		// the normal case: return one locale string
		if (! is_null($key) && is_null($val)) {
			if (isset(Solar::$locale[$class][$key])) {
				// there is a locale string
				return Solar::$locale[$class][$key];
			} else {
				// no locale string, return the key
				return $key;
			}
		}
		
		// get all locale strings for a class
		if (is_null($key) && is_null($val)) {
			if (isset(Solar::$locale[$class])) {
				return Solar::$locale[$class];
			} else {
				return array();
			}
		}
			
		// set the value of all keys for a class
		if (is_null($key) && is_array($val)) {
			Solar::$locale[$class] = $val;
			return true;
		}
		
		// set the value of one key
		if ($key && ! is_null($val)) {
			Solar::$locale[$class][$key] = $val;
			return true;
		}
		
		// something wrong
		return false;
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
	* @todo Should this be 'load' (vice 'autoload')?  It's not automatic...
	* 
	* @todo Add localization for errors
	* 
	* @access public
	* 
	* @param string $class A Solar (or other) class name.
	* 
	* @return void
	* 
	*/
	
	public static function autoload($class) 
	{
		// pre-empt searching for the class
		if (class_exists($class)) {
			return;
		}
		
		if (trim($class) == '') {
			return Solar::error(
				'Solar', // class
				'no_autoload_class', // code
				'no class named for autoload', // text
				array('class' => null), // info
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
		
		// include the file.
		include_once $file;
		
		// if the class was not in the file, we have a problem.
		if (! class_exists($class)) {
			return Solar::error(
				'Solar', // class
				'class_not_found', // code
				'class not found in autoload file', // text
				array('class' => $class, 'file' => $file), // info
				E_USER_ERROR // level
			);
		}
	}
	
	
	/**
	* 
	* Runs a script in an isolated space.
	* 
	* @access public
	* 
	* @param string A script path and file name.
	* 
	* @return mixed The final return of the included file, if any.
	* 
	*/
	
	public static function run() 
	{
		return include(func_get_arg(0));
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
		$result = Solar::autoload($class);
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
					'shared_object_name_wrong',
					"shared object name $name not in config file under ['Solar']['shared']", 
					array(),
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
	* default value. Strips slashes and HTML tags automatically.
	* 
	* @todo Allow a config group to specify the scrubbers?
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
	
	public static function get($key = null, $default = null, $scrub = null) 
	{
		// what scrubber callbacks should we use?
		if (is_null($scrub)) {
			// if no callbacks, at least strip tags
			$scrub = Solar::config('Solar', 'scrub_get', array('strip_tags'));
		}
		
		if (is_null($key) && isset($_GET)) {
		
			// no key selected, return the whole $_GET array
			return Solar::scrub($_GET, $scrub);
			
		} elseif (isset($_GET[$key])) {
		
			// looking for a specific key
			return Solar::scrub($_GET[$key], $scrub);
			
		} else {
		
			// specified key does not exist
			return $default;
			
		}
	}
	
	
	/**
	* 
	* Safely get the value of an element from the $_POST array.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value. Strips slashes (but not HTML tags) automatically.
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
	
	public static function post($key = null, $default = null, $scrub = null) 
	{
		// what scrubber callbacks should we use?
		if (is_null($scrub)) {
			// if no callbacks, don't add any
			$scrub = Solar::config('Solar', 'scrub_post', null);
		}
		
		if (is_null($key) && isset($_POST)) {
		
			// no key selected, return the whole $_POST array
			return Solar::scrub($_POST);
			
		} elseif (isset($_POST[$key])) {
		
			// looking for a specific key
			return Solar::scrub($_POST[$key]);
			
		} else {
		
			// specified key does not exist
			return $default;
			
		}
	}
	
	
	/**
	* 
	* Get the value of any element from any superglobal array.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value. Does not scrub at all.
	* 
	* @access public
	* 
	* @param string $type The superglobal type: 'server', 'cookie',
	* 'env', and so on.
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
	
	public static function super($type, $key = null, $default = null,
		$scrub = null) 
	{
		// what scrubber callbacks should we use?
		if (is_null($scrub)) {
			// if none set, don't add any
			$scrub = Solar::config('Solar', 'scrub_super', null);
		}
		
		// convert 'name' to '_NAME'
		$type = strtoupper($type);
		if (substr($type, 0, 1) != '_') {
			$type = '_' . $type;
		}
		
		// get the whole superglobal, or just one key?
		if (is_null($key) && isset($GLOBALS[$type])) {
		
			// no key selected, return the whole array
			return Solar::scrub($GLOBALS[$type], scrub);
			
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
	* Safely gets the value of $_SERVER['PATH_INFO'] element.
	* 
	* Automatically checks if the element is set; if not, returns a
	* default value. Strips slashes and HTML tags automatically.
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
	
	public static function pathinfo($key = null, $default = null, $scrub = null) 
	{
		// what scrubber callbacks should we use?
		if (is_null($scrub)) {
			// if no callbacks, at least strip tags
			$scrub = Solar::config('Solar', 'scrub_pathinfo', array('strip_tags'));
		}
		
		$info = Solar::super('_SERVER', 'PATH_INFO', '', $scrub);
		$elem = explode('/', $info);
		
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
	* Scrub a user-supplied value; if an array, do so recursively.
	* 
	* Always strips slashes if magic_quotes_gpc is turned on. Also
	* applies an array of requested functions.
	*
	* @access public
	* 
	* @param string $var The variable to scrub.
	*
	* @param array $callbacks A list of call_user_func() callbacks to
	* apply to the variable; the callbacks must take only a single
	* argument: the variable to be scrubbed.
	*
	* @return mixed The scrubbed variable.
	* 
	*/
	
	public static function scrub($var, $callbacks = null) 
	{
		// discover if magic quotes are turned on
		static $magicQuotes;
		if (! isset($magicQuotes)) {
			$magicQuotes = get_magic_quotes_gpc();
		}
		
		// if magic quotes enabled, or a function list is given,
		// continue to process the variable.
		if ($magicQuotes || $callbacks) {
		
			// is the array a variable?
			if (is_array($var)) {
			
				// is an array, so recursively scrub each element.
				foreach ($var as $k => $v) {
					$var[$k] = Solar::scrub($v, $callbacks);
				}
				
			} else {
			
				// not an array, scrub the value.
				// first, dispel magic quotes ...
				if ($magicQuotes) {
					$var = stripslashes($var);
				}
				
				// ... then apply additional functions.
				if ($callbacks) {
					foreach ($callbacks as $call) {
						$var = call_user_func($call, $var);
					}
				}
			}
		}
		
		// done, return the scrubbed variable.
		return $var;
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
	* Simple variable dumper for HTML using var_dump().
	* 
	* @todo This should be replaced by the Log class later.
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
		echo '<pre>';
		if ($label) {
			echo htmlspecialchars($label) . " ";
		}
		ob_start();
		var_dump($var);
		$output = ob_get_clean();
		$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
		echo htmlspecialchars($output);
		echo '</pre>';
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
}
?>