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
 * ++ Overview
 * 
 * The \\Solar\\ class is responsible for generating and maintaining
 * the Solar environment.  As such, it stands over and above all
 * other Solar classes in that it ties everything together in a
 * meaningful and cohesive way.
 * 
 * Unlike most other classes in Solar, the \\Solar\\ class is
 * composed entirely of static methods and objects.  This means you
 * never need to instantiate a \\Solar\\ object proper; just include
 * it.
 * 
 * <code type="php">
 * require_once 'Solar.php';
 * 
 * // never instantiate Solar ...
 * $solar = new Solar(); // improper
 * $solar->start();      // improper
 * 
 * // just call its static methods
 * Solar::start();       // correct!
 * </code>
 * 
 * You can then call any of the ClassMethods provided by Solar to
 * speed your development cycle.
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
     * @var array
     * 
     */
    static public $config = array();
    
    /**
     * 
     * Shared singleton objects are properties of the $shared object.
     * 
     * @var array
     * 
     */
    static protected $_shared = null;
    
    /**
     * 
     * Status flag (whether Solar has started or not).
     * 
     * @var bool
     * 
     */
    static protected $_status = false;
    
    /**
     * 
     * Directory where the Solar.php file is located; used for class loading.
     * 
     * Usually the same as the PEAR library directory.
     * 
     * @var bool
     * 
     */
    static protected $_dir = null;
    
    /**
     * Singleton pattern, disallow construction.
     */
    final private function __construct() {}
    
    /**
     * 
     * Starts Solar: get config, load shared objects, run start scripts.
     * 
     * This method starts the Solar environment; it is usually the
     * very first method call you make after including the Solar.php
     * file.
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * // the rest of your script
     * 
     * Solar::stop();
     * </code>
     * 
     * Note that you can specify the location of the [Main:ConfigFile
     * configuration file] as the only parameter.
     * 
     * ++ What start() Does
     * 
     * The start() method performs a huge number of activities for
     * you to set up the execution environment.  Be sure to look at
     * the Solar.php file itself for the details, but in general,
     * these activities are:
     * 
     * # Reads the [Main:ConfigFile configuration file] file into
     * Solar::$config.
     * 
     * # Processes the Solar::$config['Solar']['ini_set'] key/value
     * pairs using [[php ini_set()]].
     * 
     * # Prepares space for shared objects noted in
     * Solar::$config['Solar']['shared'] but does not instantiate
     * them; Solar waits for the first call to Solar::shared() before
     * loading and instantiating a shared object (this is called
     * "lazy loading").
     * 
     * # Instantiates the shared Solar_Locale object (for reading
     * locale strings) as Solar::shared('locale').
     * 
     * # Instantiates all objects noted in
     * Solar::$config['Solar']['autoshare'].  As part of this
     * process, Solar_User is instantiated automatically as
     * Solar::shared('user').
     * 
     * # For each auto-shared object, Solar attempts to run its
     * \\solar('start')\\ method.  This behavior is reserved only for
     * auto-shared objects; if you load a shared object later on, its
     * \\solar('start')\\ method won't be called.
     * 
     * # Finally, Solar runs any scripts noted in
     * Solar::$config['Solar']['start'].  This allows you to specify
     * additional environment startup behaviors.
     * 
     * @todo Put autosharing behavior into Solar_Controller_Front instead?
     * This would also get rid of the __solar() autoshare method, which might
     * be nice.  Would need to add a share() method for setting up shared
     * objects though.
     * 
     * @todo Rename ::shared() to ::registry()?  Add ::register() method too?
     * 
     */
    static public function start($alt_config = null)
    {
        // don't re-start if we're already running.
        if (Solar::$_status) {
            return;
        }
        
        // where are we in the file system?
        Solar::$_dir = Solar::fixdir(dirname(__FILE__));
        
        // the base for all Solar classes (except Solar itself ;-).
        require_once Solar::$_dir . 'Solar/Base.php';
        
        // the Solar_Exception class
        require_once Solar::$_dir . 'Solar/Exception.php';
        
        // the Solar_Error class, needed for Solar::isError().
        require_once Solar::$_dir . 'Solar/Error.php';
        
        // initialize $_shared property as a StdClass object
        Solar::$_shared = new StdClass;
        
        // set up the standard Solar environment
        Solar::_environment();
        
        // load the config file values. note that we use $config here,
        // not config(), because we are setting the value of the static
        // property.  use alternate config source if one is given.
        if (is_array($alt_config)) {
            Solar::$config = $alt_config;
        } elseif (is_object($alt_config)) {
            Solar::$config = (array) $alt_config;
        } elseif (is_string($alt_config)) {
            Solar::$config = (array) Solar::run($alt_config);
        } elseif ($alt_config === false) {
            // don't load any configs at all
            Solar::$config = array();
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
            $class = get_class(Solar::$_shared->$name);
            if (is_callable($class, 'solar')) {
                Solar::$_shared->$name->solar('start');
            }
        }
        
        // finally, run any 'start' hook scripts
        foreach (Solar::config('Solar', 'start', array()) as $file) {
            Solar::run($file);
        }
        
        // and we're done!
        Solar::$_status = true;
    }
    
    /**
     * 
     * Stops Solar: run stop scripts and shared object "stop" hooks.
     * 
     * Stops the Solar environment.  As a counterpart to
     * Solar::start(), the stop() method shuts down the Solar
     * environment.
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * // the rest of the script goes here
     * 
     * Solar::stop();
     * </code>
     * 
     * Like Solar::start(), the stop() method performs a number of
     * activities for you.  Be sure to read the Solar.php script file
     * for specifics, but in general, the stop() activities are:
     * 
     * # Execute any scripts named in
     * Solar::$config['Solar']['stop'].  This allows you to run
     * shutdown scripts particular to your system.
     * 
     * # For each auto-shared object, run its \\solar('stop')\\
     * method.
     * 
     *  * This behavior applies only to auto-shared objects from
     * Solar::start(); shared objects that were not auto-shared at
     * startup time are not included in the stop() method.
     * 
     *  * The auto-shared objects are shut down in a last-in first-out
     * order.  This means the last auto-shared object is the first to
     * be stopped.
     * 
     * @return void
     * 
     */
    static public function stop()
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
            $class = get_class(Solar::$_shared->$name);
            if (is_callable($class, 'solar')) {
                Solar::$_shared->$name->solar('stop');
            }
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
    static public function apiVersion()
    {
        return '@package_version@';
    }
    
    /**
     * 
     * Gets translated locale string for a class and key.
     * 
     * This method allows you to retrieve the proper text string as
     * related to a specific class for a given locale.  Be sure to
     * read about [Main:LocaleFiles locale files] for more
     * information.
     * 
     * For example, to get the locale string for the 'HELLO' key in
     * the 'Example' class ...
     * 
     * <code type="php">
     * $text = Solar::locale('Example', 'HELLO');
     * </code>
     * 
     * By default, the locale() method retrieves the singluar string
     * for the translation key; if you need the "zero" or "plural"
     * strings, just pass the appropriate number as the third
     * parameter, and the locale() method will get the correct
     * translation (provided it has been set up in the locale file).
     * 
     * <code type="php">
     * // get the translation for "0 apples"
     * $num = 0;
     * $text = Solar::locale('Example', 'APPLE', $num);
     * 
     * // get the translation for "1 apple"
     * $apples = 1;
     * $text = Solar::locale('Example', 'APPLE', $num);
     * 
     * // get the translation for "2 (or more) apples"
     * $apples = 2;
     * $text = Solar::locale('Example', 'APPLE', $num);
     * </code>
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
    static public function locale($class, $key, $num = 1)
    {
        return Solar::shared('locale')->string($class, $key, $num);
    }
    
    /**
     * 
     * Loads a class file but does not create an object instance.
     * 
     * In normal PHP, you would load a class file using include or
     * require, then attempt to instantiate the class.
     * 
     * <code type="php">
     * include_once "My/Example/Class.php";
     * $example = new My_Example_Class();
     * </code>
     * 
     * However, if My/Example/Class.php did not have the
     * My_Example_Class in it, PHP would throw an error; this can
     * often be a difficult error to track down.
     * 
     * The loadClass() method is the equivalent of the above pair of
     * steps, but adds a [[php class_exists()]] check to see if the
     * class was actually loaded from the file.  It won't attempt to
     * load the same class file more than once.
     * 
     * For example:
     * 
     * <code type="php">
     * Solar::loadClass('My_Example_Class');
     * </code>
     * 
     * If after calling loadClass() the 'My_Example_Class' still does
     * not exist, Solar throws a fatal error, with a backtrace, to
     * let you know the load failed.  This makes it easy to track
     * down failed loads.
     * 
     * For this method to work, the class to be loaded must be in the
     * include_path and conform to the [Main:NamingConventions class
     * naming conventions].  Note that when using Solar::object(),
     * you **do not** need to use loadClass() first; Solar::object()
     * automatically calls loadClass() to load the requested class
     * file.
     * 
     * @param string $class A Solar (or other) class name.
     * 
     * @return void
     * 
     * @todo Add localization for errors
     * 
     * @todo Add 'strict' flag to not-prepend self::$_dir?
     * 
     */
    static public function loadClass($class)
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
        
        // include the file from the Solar dir and check for failure. we
        // use run() here instead of require() so we can see the
        // exception backtrace.
        $result = Solar::run(Solar::$_dir . $file);
        
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
     * Runs a script in an isolated scope.
     * 
     * Use the run() method to include a file inside its own limited
     * scope.  This allows you to include files but not pollute the
     * current scope with the variables from the included file.
     * 
     * For example, say you have this "main" script:
     * 
     * <code type="php">
     * $var = 'foo';
     * include 'helper.php';
     * echo $var;
     * </code>
     * 
     * If 'helper.php' sets the value of $var for itself, that value
     * will override the value from the main script, which can lead
     * to unexpected behavior.  If you want to make sure that
     * 'helper.php' executes in its own separate scope, use the run()
     * method instead of [[php include]]:
     * 
     * <code type="php">
     * $var = 'foo';
     * Solar::run('helper.php');
     * echo $var;
     * </code>
     * 
     * Now $var will remain the same before and after the inclusion
     * of helper.php (unless helper.php used [[php global]] to make
     * $var global).
     * 
     * As with [[php include]], you can accept return values from the
     * file you run:
     * 
     * <code type="php">
     * $result = Solar::run('helper.php');
     * </code>
     * 
     * If the last line of helper.php is a return value, $result will
     * reflect that value.
     *      
     * @param string $file A script path and file name.
     * 
     * @return mixed The final return of the included file.
     * 
     */
    static public function run($file)
    {
        if (Solar::fileExists($file)) {
            // clean up the local scope, then
            // include the file and return its results
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
     * Hack for file_exists() && is_readable() that checks the include_path.
     * 
     * Use this to see if a file exists anywhere in the include_path.
     * 
     * <code type="php">
     * $file = 'include/path/to/file.php';
     * if (Solar::fileExists('include/path/to/file.php')) {
     *     include $file;
     * }
     * </code>
     * 
     * @param string $file A script path and file name.
     * 
     * @return bool True if the file exists and is readble in the
     * include_path, false if not.
     * 
     */
    static public function fileExists($file)
    {
        $fp = @fopen($file, 'r', true);
        $ok = ($fp) ? true : false;
        @fclose($fp);
        return $ok;
    }
    
    /**
     * 
     * Convenience method to instantiate and configure a Solar object.
     * 
     * Creates a new object instance, auto-configuring it from
     * user-defined parameters and the Solar.config.php file.
     * 
     * In normal PHP, you must include a class file and then
     * instantiate it.
     * 
     * <code type="php">
     * // normal use
     * include_once 'My/Class/File.php';
     * $obj = new My_Class_File();
     * </code>
     * 
     * With Solar, if you want to instantiate a standalone object,
     * you can use the Solar::factory() method instead of the
     * include-and-instantiate routine.
     * 
     * <code type="php">
     * // Solar object factory
     * $obj = Solar::factory('My_Class_File');
     * </code>
     * 
     * This checks the include-path to see if the My/Class/File.php
     * file exists, and throws a warning (with backtrace) if it does
     * not.
     * 
     * If the class conforms to the Solar standards for
     * [Main:ConstructorParameters constructor parameters], the class
     * will be configured automatically with its corresponding values
     * in the [Main:ConfigFile config file].  If you want to override
     * those values, you can pass a custom config array as the second
     * parameter:
     * 
     * <code type="php">
     * $options = array('zim' => 'gir', 'baz' => 'dib');
     * $obj = Solar::factory('My_Class_File', $options);
     * </code>
     * 
     * For this method to work, the class to be loaded must be in the
     * include_path and conform to the [Main:NamingConventions class
     * naming conventions].
     * 
     * @param string $class The class name.
     * 
     * @param array $config The configuration array for the class.
     * 
     * @return object A new instance of the requested Solar class.
     * 
     */
    static public function factory($class, $config = null)
    {
        $result = Solar::loadClass($class);
        $obj = new $class($config);
        return $obj;
    }
    
    /**
     * 
     * Convenience method to instantiate a shared (singleton) object.
     * 
     * @param string $name The shared singleton name.
     * 
     * @return object A singleton instance of the requested Solar class.
     * 
     * @todo Localize these errors.
     * 
     */
    static public function shared($name)
    {
        // has the shared object already been loaded?
        if (! isset(Solar::$_shared->$name)) {
            
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
                Solar::$_shared->$name = Solar::factory($class, $config);
                
            } else {
            
                // did not find the info.  that's an exception.
                throw Solar::exception(
                    'Solar',
                    'ERR_SHARED_NAME',
                    "Shared object name '$name' not in config file under ['Solar']['shared']", 
                    array('shared' => $name)
                );
                
            }
        }
        
        // return the shared instance.
        return Solar::$_shared->$name;
    }
    
    /**
     * 
     * Safely gets a configuration group array or element value.
     * 
     * ++ Reading An Entire Group
     * 
     * If your Solar.config.php file has this entry ...
     * 
     * <code type="php">
     * $config['Example'] = array(
     *   'flag_a'  => 'these',
     *   'flag_b'  => 'those',
     *   'deeper'  => array(
     *     'deep_1' => 'foo',
     *     'deep_2' => 'bar',
     *   ),
     * );
     * </code>
     * 
     * ... you can retrieve a copy of the entire 'Example' group like this:
     * 
     * <code type="php">
     * $example = Solar::config('Example');
     * </code>
     * 
     * If the 'Example' group does not exist, the config() method
     * will return an empty array by default.  If you want to use a
     * different default value when 'Example' does not exist, specify
     * a \\null\\ element and the customized default value:
     * 
     * <code type="php">
     * $default = Solar::object('Solar_Error');
     * $example = Solar::config('Example', null, $default);
     * </code>
     * 
     * Thus, \\$example\\ will be a Solar_Error if 'Example' does not
     * exist in the config file.
     * 
     * 
     * ++ Reading A Single Group-Element
     * 
     * If your Solar.config.php file has this entry (identical to the
     * above example)...
     * 
     * <code type="php">
     * $config['Example'] = array(
     *   'flag_a'  => 'these',
     *   'flag_b'  => 'those',
     *   'deeper'  => array(
     *     'deep_1' => 'foo',
     *     'deep_2' => 'bar',
     *   ),
     * );
     * </code>
     * 
     * ... you can retrieve a copy of the 'flag_a' value like this:
     * 
     * <code type="php">
     * $flag_a = Solar::config('Example', 'flag_a');
     * </code>
     * 
     * If the 'Example' group does not exist, or if the 'flag_a'
     * element does not exist in the 'Example' group, the config()
     * method will return \\null\\ value by default.  If you want to
     * use a different default value, specify a that value as the
     * third parameter:
     * 
     * <code type="php">
     * $flag_a = Solar::config('Example', 'flag_a', 'thars');
     * </code>
     * 
     * Thus, \\$example\\ will be \\false\\ if
     * Solar::$config['Example']['flag_a'] does not exist.
     * 
     * ++ Deep Reading
     * 
     * The config() method only allows you to read groups, or major
     * group elements.  If you have this in your config file (again,
     * identical to above) ...
     * 
     * <code type="php">
     * $config['Example'] = array(
     *   'flag_a'  => 'these',
     *   'flag_b'  => 'those',
     *   'deeper'  => array(
     *     'deep_1' => 'foo',
     *     'deep_2' => 'bar',
     *   ),
     * );
     * </code>
     * 
     * ... you can retrieve the 'deeper' element,
     * 
     * <code type="php">
     * $deeper = Solar::config('Example', 'deeper');
     * </code>
     * 
     * ... but you cannot retrieve the 'deep_1' sub-element.  In
     * practice, this is not usually an issue.  Although you can
     * always access Solar::$config if you need to, nesting
     * often-used config file elements too deeply may be a signal
     * that you need to re-think your design.     *
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
    static public function config($group, $elem = null)
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
     * This method accesses the $_GET superglobal array and returns a
     * copy of the requested key.  If no key is specified,
     * a copy of the entire $_GET array is returned.  If the
     * requested key does not exist in the $_GET array, the default
     * value is returned instead.
     * 
     * For example, to get the 'user_name' key from the $_GET array,
     * with 'No Name' as the default value, you would do this:
     * 
     * <code type="php">
     * $name = Solar::get('user_name', 'No Name');
     * </code>
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
    static public function get($key = null, $default = null)
    {
        return Solar::_super('_GET', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_POST array.
     * 
     * This method accesses the $_POST superglobal array and returns
     * a copy of the requested key.  If no key is
     * specified, a copy of the entire $_POST array is
     * returned.  If the requested key does not exist in the $_POST
     * array, the default value is returned instead.
     * 
     * For example, to get the 'subject_line' key from the $_POST
     * array, with 'No Subject' as the default value, you would do
     * this:
     * 
     * <code type="php">
     * $subject = Solar::post('subject_line', 'No Subject');
     * </code>

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
    static public function post($key = null, $default = null)
    {
        return Solar::_super('_POST', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_COOKIE array.
     * 
     * This method accesses the $_COOKIE superglobal array and
     * returns a copy of the requested key.  If no key is
     * specified, a copy of the entire $_COOKIE array is
     * returned.  If the requested key does not exist in the $_COOKIE
     * array, the default value is returned instead.
     * 
     * For example, to get the 'remember_me' key from the $_COOKIE
     * array, with \\false\\ as the default value, you would do this:
     * 
     * <code type="php">
     * $remember_me = Solar::cookie('remember_me', false);
     * </code>

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
    static public function cookie($key = null, $default = null)
    {
        return Solar::_super('_COOKIE', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_SERVER array.
     * 
     * This method accesses the $_SERVER superglobal array and
     * returns a copy of the requested key.  If no key is
     * specified, a copy of the entire $_SERVER array is
     * returned.  If the requested key does not exist in the $_SERVER
     * array, the default value is returned instead.
     * 
     * For example, to get the 'REQUEST_URI' key from the $_SERVER
     * array, with \\false\\ as the default value, you would do this:
     * 
     * <code type="php">
     * $remember_me = Solar::server('REQUEST_URI', false);
     * </code>

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
    static public function server($key = null, $default = null)
    {
        return Solar::_super('_SERVER', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of an element from the $_SESSION array.
     * 
     * This method accesses the $_SESSION superglobal array and
     * returns a copy of the requested key.  If no key is
     * specified, a copy of the entire $_SESSION array is
     * returned.  If the requested key does not exist in the
     * $_SESSION array, the default value is returned instead.
     * 
     * For example, to get the 'last_active' key from the $_SESSION
     * array, with \\false\\ as the default value, you would do this:
     * 
     * <code type="php">
     * $last_active = Solar::session('last_active', false);
     * </code>

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
    static public function session($key = null, $default = null)
    {
        return Solar::_super('_SESSION', $key, $default);
    }
    
    /**
     * 
     * Safely gets the value of $_SERVER['PATH_INFO'] element.
     * 
     * This method is a bit different from the other scrubber
     * methods.  It accesses the $_SERVER['PATH_INFO'] value, and
     * returns copy of the requested key.  If no key is
     * specified, the entire path-info set is returned as an array.
     * 
     * The "path-info" portion of a URI comes after the script name
     * but before any $_GET parameters.  For example, in the
     * following URI ...
     * 
     * <code>
     * http://example.com/path/to/script.php/foo/bar/baz?zim=gir
     * </code>
     * 
     * ... the "path-info" is \\/foo/bar/baz\\.
     * 
     * Path-info values are addressed by their integer position
     * number, not by associative array key name (as with $_GET et.
     * al.).  Thus, Solar builds its path-info array for the example
     * URI to look like this:
     * 
     * <code type="php">
     * array(
     *   0 => 'foo',
     *   1 => 'bar',
     *   2 => 'baz',
     * );
     * </code>
     * 
     * For example, to get path-info values, you would call the
     * pathinfo() method like this:
     * 
     * <code type="php">
     * $info_2 = Solar::pathinfo(2, null);  // equals 'baz'
     * $info_3 = Solar::pathinfo(3, 'dib'); // equals 'dib'
     * </code>

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
    static public function pathinfo($key = null, $default = null)
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
     * This method generates a Solar_Exception object with an originating
     * class, an error code, an error message, and an array of information
     * about the specifics of the error.
     * 
     * Note that this method only generates the object; it does not
     * throw the exception.
     * 
     * <code type="php">
     * $class = 'My_Example_Class';
     * $code = 'ERR_SOMETHING_WRONG';
     * $text = 'Something is wrong.';
     * $info = array('foo' => 'bar');
     * $exception = Solar::exception($class, $code, $text, $info);
     * throw $exception;
     * </code>
     * 
     * In general, you shouldn't need to use this directly in classes
     * extended from [Solar_Base:HomePage Solar_Base].  Instead, use
     * $this->_exception($code, $info) for automated picking of the
     * right exception class from the $code, and automated translation
     * of the error message.
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
    static public function exception($class, $code, $text = '', $info = array())
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
     * applies some simple formatting for readability, and [[php
     * echo]]s it tags with an optional label.  Use this for
     * debugging variables to see exactly what they contain.
     * 
     * @param mixed &$var The variable to dump.
     * 
     * @param string $label A label for the dumped output.
     * 
     * @return void
     * 
     */
    static public function dump(&$var, $label = null)
    {
        $obj = Solar::factory('Solar_Debug_Var');
        echo $obj->dump($var, $label);
    }
    
    /**
     * 
     * Fixes a directory string for the operating system.
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
    static public function fixdir($dir)
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
     * @return void
     * 
     */
    static protected function _environment()
    {
        // clear out registered globals?
        // (this code from Richard Heyes and Stefan Esser)
        if (ini_get('register_globals')) {
            
            /* // previous version
            // Variables that shouldn't be unset
            $keep = array('GLOBALS', '_GET', '_POST', '_COOKIE',
                '_REQUEST', '_SERVER', '_ENV', '_FILES');
            
            // Sources of global input
            $input = array_merge($_GET,    $_POST, $_COOKIE, $_SERVER,
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
    static protected function _dispelQuotes(&$value)
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
    static protected function _dispelSybase(&$value)
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
    static protected function _super($type, $key = null, $default = null)
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