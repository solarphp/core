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
 * 
 * The Solar arch-class provides static methods needed throughout the Solar environment.
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
     * Default keys are:
     * 
     * : \\locale\\ : (string) Path to the Solar locale strings directory, default 'Solar/Locale'.
     * 
     * : \\locale_code\\ : (string) The locale code Solar is using, default 'en_US'.
     * 
     * @var array
     * 
     */
    public static $config = array(
        'locale'      => 'Solar/Locale/',
        'locale_code' => 'en_US',
    );
    
    /**
     * 
     * Object registry.
     * 
     * Objects are registered using Solar::register(); the registry
     * array is keyed on the name of the registered object.
     * 
     * Although this property is public, you generally shouldn't need
     * to manipulate it in any way.
     * 
     * @var array
     * 
     */
    public static $registry = array();
    
    /**
     * 
     * Status flag (whether Solar has started or not).
     * 
     * @var bool
     * 
     */
    protected static $_status = false;
    
    /**
     * 
     * Locale strings for all classes.
     * 
     * This is where locale strings for Solar are kept.  The array
     * is keyed first by the class name, and the sub-keys are the
     * translation keys.
     * 
     * Although this property is public, you generally shouldn't need
     * to manipulate it in any way.
     * 
     * @var array
     * 
     */
    public static $locale = array();
    
    
    /**
     * 
     * The current locale code being used by Solar.
     * 
     * Default value is 'en_US'.
     * 
     * @var string 
     * 
     */
    protected static $_locale_code = 'en_US';
    
    /**
     * 
     * Constructor is disabled to enforce a singleton pattern.
     * 
     */
    final private function __construct() {}
    
    /**
     * 
     * Starts Solar: loads configuration values and and sets up the environment.
     * 
     * @param mixed $config An alternative configuration parameter.
     * 
     * @return void
     * 
     * @todo Keep the locale code in $_SESSION?
     * 
     */
    public static function start($config = null)
    {
        // don't re-start if we're already running.
        if (Solar::$_status) {
            return;
        }
        
        // needed for exceptions
        Solar::loadClass('Solar_Exception');
        
        // needed for all sub-classes
        Solar::loadClass('Solar_Base');
        
        // do some security on globals, and turn off all magic quotes
        Solar::_globalsQuotes();
        
        // load the config file values. note that we use Solar::$config here,
        // not Solar::config(), because we are setting the value of the static
        // property.  use alternate config source if one is given.
        if (is_array($config) || is_object($config)) {
            // merge from array or object
            Solar::$config = array_merge(
                Solar::$config,
                (array) $config
            );
        } elseif (is_string($config)) {
            // merge from array file return
            Solar::$config = array_merge(
                Solar::$config,
                (array) Solar::run($config)
            );
        } elseif ($config === false) {
            // leave Solar::$config alone
        } else {
            // use the default config path
            Solar::$config = array_merge(
                Solar::$config,
                (array) Solar::run(SOLAR_CONFIG_PATH)
            );
        }
        
        // process ini settings from config file
        $settings = Solar::config('Solar', 'ini_set', array());
        foreach ($settings as $key => $val) {
            ini_set($key, $val);
        }
        
        // load the initial locale strings
        Solar::$_locale_code = Solar::$config['locale_code'];
        Solar::setLocale(Solar::$_locale_code);
        
        // run any 'start' hook scripts
        foreach ((array) Solar::config('Solar', 'start') as $file) {
            Solar::run($file);
        }
        
        // start the session if one hasn't been started already
        if (session_id() === '') {
            session_start();
        }
        
        // and we're done!
        Solar::$_status = true;
    }
    
    /**
     * 
     * Stops Solar: run stop scripts.
     * 
     * @return void
     * 
     */
    public static function stop()
    {
        // run the user-defined stop scripts.
        foreach ((array) Solar::config('Solar', 'stop') as $file) {
            Solar::run($file);
        }
        
        // reset the status flag, and we're done.
        Solar::$_status = false;
    }
    
    /**
     * 
     * Returns the API version for Solar.
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
     * Gets the translated locale string for a class and key.
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
        // if the key does not exist for the class,
        // return the key itself.
        if (! isset(Solar::$locale[$class][$key])) {
            return $key;
        }
        
        // get the translation of the key and force
        // to an array.
        $string = (array) Solar::$locale[$class][$key];
        
        // return the number-appropriate version of the
        // translated key, if multiple values exist.
        if ($num != 1 && isset($string[1])) {
            return $string[1];
        } else {
            return $string[0];
        }
    }
    
    /**
     * 
     * Sets the locale code and clears out previous locale strings.
     * 
     * @param string $code A locale code, e.g., 'en_US'.
     * 
     * @return void
     */
    public static function setLocale($code)
    {
        // set the code
        Solar::$_locale_code = $code;
        
        // reset the strings
        Solar::$locale = array();
        
        // load the base Solar locale strings
        $dir = Solar::fixdir(Solar::$config['locale']);
        $file = $dir . Solar::$_locale_code . '.php';
        
        // can we find the file?
        if (Solar::fileExists($file)) {
            Solar::$locale['Solar'] = (array) include $file;
            return true;
        } else {
            // could not find file.
            // fail silently, as it's often the case that the
            // translation file simply doesn't exist.
            Solar::$locale['Solar'] = array();
            return false;
        }
    }
    
    /**
     * 
     * Returns the current locale code.
     * 
     * @return string The current locale code, e.g., 'en_US'.
     * 
     */
    public static function getLocale()
    {
        return Solar::$_locale_code;
    }
    
    
    /**
     * 
     * Loads a class file from the include_path.
     * 
     * @param string $class A Solar (or other) class name.
     * 
     * @return void
     * 
     * @todo Add localization for errors
     * 
     */
    public static function loadClass($class)
    {
        // pre-empt searching for the class
        if (class_exists($class)) {
            return;
        }
        
        // did we ask for a non-blank class?
        if (trim($class) == '') {
            throw Solar::exception(
                'Solar',
                'ERR_LOADCLASS_EMPTY',
                'No class named for loading',
                array('class' => $class)
            );
        }
        
        // convert the class name to a file path.
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        
        // include the file and check for failure. we use run() here
        // instead of require() so we can see the exception backtrace.
        $result = Solar::run($file);
        
        // if the class was not in the file, we have a problem.
        if (! class_exists($class)) {
            throw Solar::exception(
                'Solar',
                'ERR_LOADCLASS_EXIST',
                'Class does not exist in loaded file',
                array('class' => $class, 'file' => $file)
            );
        }
    }
    
    /**
     * 
     * Uses [[php include()]] to run a script in an isolated scope.
     * 
     * @param string $file The file to include.
     * 
     * @return mixed The return value of the included file.
     * 
     */
    public static function run($file)
    {
        if (Solar::fileExists($file)) {
            // clean up the local scope, then include the file and
            // return its results
            unset($file);
            return include(func_get_arg(0));
        } else {
            // could not open the file for reading
            throw Solar::exception(
                'Solar',
                'ERR_FILE_NOT_READABLE',
                'File does not exist or is not readable',
                array('file' => $file)
            );
        }
    }
    
    /**
     * 
     * Hack for [[php file_exists()]] that checks the include_path.
     * 
     * Use this to see if a file exists anywhere in the include_path.
     * 
     * <code type="php">
     * $file = 'path/to/file.php';
     * if (Solar::fileExists('path/to/file.php')) {
     *     include $file;
     * }
     * </code>
     * 
     * @param string $file Check for this file in the include_path.
     * 
     * @return bool True if the file exists and is readble in the
     * include_path, false if not.
     * 
     */
    public static function fileExists($file)
    {
        $fp = @fopen($file, 'r', true);
        $ok = ($fp) ? true : false;
        @fclose($fp);
        return $ok;
    }
    
    /**
     * 
     * Convenience method to instantiate and configure an object.
     * 
     * @param string $class The class name.
     * 
     * @param array $config Additional configuration array for the class.
     * 
     * @return object A new instance of the requested class.
     * 
     */
    public static function factory($class, $config = null)
    {
        Solar::loadClass($class);
        $obj = new $class($config);
        return $obj;
    }
    
    /**
     * 
     * Accesses an object in the registry.
     * 
     * @param string $key The registered name.
     * 
     * @return object The object registered under $key.
     * 
     * @todo Localize these errors.
     * 
     */
    public static function registry($key)
    {
        // has the shared object already been loaded?
        if (! Solar::inRegistry($key)) {
            throw Solar::exception(
                'Solar',
                'ERR_NOT_IN_REGISTRY',
                "Object with name '$key' not in registry.",
                array('name' => $key)
            );
        }
        
        // was the registration for a lazy-load?
        if (is_array(Solar::$registry[$key])) {
            $val = Solar::$registry[$key];
            $obj = Solar::factory($val[0], $val[1]);
            Solar::$registry[$key] = $obj;
        }
        
        // done
        return Solar::$registry[$key];
    }
    
    /**
     * 
     * Registers an object under a unique name.
     * 
     * @param string $key The name under which to register the object.
     * 
     * @param object|string $spec The registry specification.
     * 
     * @param mixed $config If lazy-loading, use this as the config.
     * 
     * @return void
     * 
     * @todo Localize these errors.
     * 
     */
    public static function register($key, $spec, $config = null)
    {
        if (Solar::inRegistry($key)) {
            // name already exists in registry
            $class = get_class(Solar::$registry[$key]);
            throw Solar::exception(
                'Solar',
                'ERR_REGISTRY_NAME_EXISTS',
                "Object with '$key' of class '$class' already in registry", 
                array('name' => $key, 'class' => $class)
            );
        }
        
        // register as an object, or as a class and config?
        if (is_object($spec)) {
            // directly register the object
            Solar::$registry[$key] = $spec;
        } elseif (is_string($spec)) {
            // register a class and config for lazy loading
            Solar::$registry[$key] = array($spec, $config);
        } else {
            throw Solar::exception(
                'Solar',
                'ERR_REGISTRY_FAILURE',
                'Please pass an object, or a class name and a config array',
                array()
            );
        }
    }
    
    /**
     * 
     * Check to see if an object name already exists in the registry.
     * 
     * @param string $key The name to check.
     * 
     * @return bool
     * 
     */
    public static function inRegistry($key)
    {
        return ! empty(Solar::$registry[$key]);
    }
    
    /**
     * 
     * Returns a dependency object.
     * 
     * @param string $class The dependency object should be an instance of this class.
     * 
     * @param mixed $spec If an object, check to make sure it's an instance of $class. If a string,
     * treat as a Solar::registry() key. Otherwise, use this as a config
     * param to Solar::factory() to create a $class object.
     * 
     * @return object The dependency object.
     * 
     */
    public static function dependency($class, $spec)
    {
        // if it's a string, assume it's the key name for a registered
        // object.  get it, then proceed to class-check.
        if (is_string($spec)) {
            $spec = Solar::registry($spec);
        }
        
        // is it an object?
        if (is_object($spec)) {
            // make sure it's of the proper class
            Solar::loadClass($class);
            if (! $spec instanceof $class) {
                $actual = get_class($spec);
                throw Solar::exception(
                    'Solar',
                    'ERR_DEPENDENCY_MISMATCH',
                    "Dependency of class '$class' needed, actually '$actual'",
                    array('class' => $class, 'actual' => $actual)
                );
            }
            // it's good, return as-is
            return $spec;
        }
        
        // try to create an object with $spec as the config
        return Solar::factory($class, $spec);
    }
    
    /**
     * 
     * Safely gets a configuration group array or element value.
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
     * Safely gets the value of an element from the $_GET array.
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
        return Solar::_super('_GET', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_POST array.
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
        return Solar::_super('_POST', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_COOKIE array.
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
        return Solar::_super('_COOKIE', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_SERVER array.
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
        return Solar::_super('_SERVER', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_SESSION array.
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
        return Solar::_super('_SESSION', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of $_SERVER['PATH_INFO'] element.
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
        $info = Solar::_super('_SERVER', 'PATH_INFO', '');
        
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
     * Generates a simple exception, but does not throw it.
     * 
     * @param string $class The class that generated the exception.
     * 
     * @param mixed $code A scalar error code, generally a string.
     * 
     * @param string $text Any error message text.
     * 
     * @param array $info Additional error information in an associative
     * array.
     * 
     * @return object A Solar_Exception object.
     * 
     */
    public static function exception($class, $code, $text = '', $info = array())
    {
        return Solar::factory('Solar_Exception', array(
            'class' => $class,
            'code'  => $code,
            'text'  => $text,
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Dumps a variable to output.
     * 
     * Essentially, this is an alias to the Solar_Debug_Var::dump()
     * method, which buffers the [[php var_dump]] for a variable,
     * applies some simple formatting for readability, [[php echo]]s
     * it, and prints with an optional label.  Use this for
     * debugging variables to see exactly what they contain.
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
        $obj = Solar::factory('Solar_Debug_Var');
        echo $obj->dump($var, $label);
    }
    
    /**
     * 
     * "Fixes" a directory string for the operating system.
     * 
     * Use slashes anywhere you need a directory separator.
     * Then run the string through fixdir() and the slashes will be converted
     * to the proper separator (e.g. '\' on Windows).
     * 
     * Always adds a final trailing separator.
     * 
     * @param string $dir The directory string to 'fix'.
     * 
     * @return string The "fixed" directory string.
     * 
     */
    public static function fixdir($dir)
    {
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    
    /**
     * 
     * Sets a "read-once" session value for a class and key.
     * 
     * Taken from ideas popularized by Ruby on Rails, a "flash" is a session
     * value that propagates only until it is read, at which time it
     * is removed from the session.  This is useful for forwarding
     * information and messages between page loads.
     * 
     * @param string $class The related class for the flash.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public static function setFlash($class, $key, $val)
    {
        $_SESSION['Solar']['flash'][$class][$key] = $val;
    }
    
    /**
     * 
     * Appends a "read-once" session value to a class and key.
     * 
     * @param string $class The related class for the flash.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public static function addFlash($class, $key, $val)
    {
        if (! isset ($_SESSION['Solar']['flash'][$class][$key])) {
            $_SESSION['Solar']['flash'][$class][$key] = array();
        }
        
        if (! is_array($_SESSION['Solar']['flash'][$class][$key])) {
            settype($_SESSION['Solar']['flash'][$class][$key], 'array');
        }
        
        $_SESSION['Solar']['flash'][$class][$key][] = $val;
    }
    
    /**
     * 
     * Retrieves a "read-once" session value, thereby removing the value.
     * 
     * @param string $class The related class for the flash.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val If the class and key do not exist, return
     * this value.  Default null.
     * 
     * @return mixed The "read-once" value.
     * 
     */
    public static function getFlash($class, $key, $val = null)
    {
        if (isset($_SESSION['Solar']['flash'][$class][$key])) {
            $val = $_SESSION['Solar']['flash'][$class][$key];
            unset($_SESSION['Solar']['flash'][$class][$key]);
        }
        return $val;
    }
    
    /**
     * 
     * Performs some security on globals, removes magic quotes if turned on.
     * 
     * @return void
     * 
     */
    protected static function _globalsQuotes()
    {
        // clear out registered globals?
        // (this code from Richard Heyes and Stefan Esser)
        if (ini_get('register_globals')) {
            
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
                $func = array('Solar', '_dispelSybase');
            } else {
                // "normal" slashed quotes
                $func = array('Solar', '_dispelQuotes');
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
    protected static function _dispelQuotes(&$value)
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
    protected static function _dispelSybase(&$value)
    {
        $value = str_replace("''", "'", $value);
    }
    
    /**
     * 
     * Fetches a superglobal value by key, or a default value.
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
    protected static function _super($type, $key = null, $default = null)
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