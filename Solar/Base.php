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
 * This is the class from which almost all other Solar classes are
 * extended.  Solar_Base is relatively light, and provides:
 * 
 * * Construction-time reading of [Main:ConfigFile config file] options 
 *   for itself, and merging of those options with any options passed   
 *   for instantation, along with the class-defined $_config defaults,  
 *   into the Solar_Base::$_config property.                            
 *                                                                     
 * * A Solar_Base::locale() convenience method to return class-specific
 *   locale strings.  This method...
 * 
 *  # Automatically loads the translated strings from the correct file,
 * 
 *  # Automatically re-loads them if the locale code changes,
 * 
 *  # Automatically falls back to the "generic" Solar-wide translations
 *    if a class-specific translation key does not exist.
 * 
 * * A Solar_Base::_exception() convenience method to generate
 *   exception objects with translated strings from the locale file
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
     * Config keys are:
     * 
     * : \\locale\\ : (string) Directory where locale files for the class are kept
     * 
     * The $_config property has a special purpose within Solar
     * classes: it contains the configuration parameters for
     * instantiating an object.  When you extend Solar_Base and
     * instantiate the subclassed object, any key in this array will
     * be re-populated with related keys from the [Main:ConfigFile
     * config file], and/or from keys set in the configuration
     * parameter of Solar::factory() at instantiation time.
     * 
     * Within $_config, the \\locale\\ key itself has a special
     * purpose; the Solar_Base::locale() method uses it to determine
     * where localization files related to the class are stored. 
     * Thus, if you have a 'locale' key in your $_config array, it
     * should always be a string that tells where the
     * [Main:LocaleFiles locale files] are.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale' => null,
    );
    
    /**
     * 
     * Constructor.
     * 
     * The Solar_Base constructor does quite a bit within Solar,
     * particularly by reading the [Main:ConfigFile config file]
     * values for the class.
     * 
     * ++ Extending the Constructor
     * 
     * When you extend the constructor, make sure the only parameter
     * is \\$config = null\\ (this is how it receives
     * instantiation-time configuration values) and that it calls the
     * parent constructor at some point (with the \\$config\\ parameter
     * passed up the chain).
     * 
     * For example:
     * 
     * <code type="php">
     * class Example extends Solar_Base {
     *     protected $_config = array(
     *         'opt_1' => 'foo',
     *         'opt_2' => 'bar',
     *         'opt_3' => 'baz'
     *     );
     * 
     *     public __construct($config = null)
     *     {
     *         // set up the 'locale' option for $config ...
     *         $this->_config['locale'] = dirname(__FILE__) . '/Locale/';
     *         
     *         // ... and continue construction.
     *         parent::__construct($config);
     *     }
     * }
     * </code>
     * 
     * ++ Using The $_config Property
     * 
     * When you define a Solar_Base extended class, you will need to
     * populate the Solar_Base::$_config array with all of the
     * options and keys you want the user to be able to configure. 
     * Let's say we want those configuration options to be called
     * "opt_1", "opt_2", and "opt_3" (as a generic example).  You
     * would set up your extended class to define those options as
     * part of the $_config property.
     * 
     * <code type="php">
     * class Example extends Solar_Base {
     *     protected $_config = array(
     *         'opt_1' => 'foo',
     *         'opt_2' => 'bar',
     *         'opt_3' => 'baz'
     *     );
     * }
     * </code>
     * 
     * When you use Solar::factory() to instantiate this class, those
     * will be the default $_config values.
     * 
     * <code type="php">
     * 
     * $example = Solar::factory('Example');
     * 
     * // The values of $example->_config are as listed above:
     * //
     * // 'opt_1' => 'foo'
     * // 'opt_2' => 'bar'
     * // 'opt_3' => 'baz'
     * 
     * </code>
     * 
     * +++ Config File Settings
     * 
     * Now, if your [Main:ConfigFile config file] has an 'Example'
     * group in it, those values will override any of the default
     * values set by your class definition.  Say your config file
     * looks something like this:
     * 
     * <code type="php">
     * $config = array();
     * // ...
     * $config['Example']['opt_3'] = 'dib';
     * // ...
     * return $config;
     * </code>
     * 
     * When you instantiate the Example object, the config file value
     * will override the default value, leaving all others in place:
     * 
     * <code type="php">
     * 
     * $example = Solar::factory('Example');
     * 
     * // The values of $example->_config are now:
     * // 
     * // 'opt_1' => 'foo'
     * // 'opt_2' => 'bar'
     * // 'opt_3' => 'dib' ... not 'baz' because it was set in Solar.config.php
     * 
     * </code>
     * 
     * +++ Instantiation Settings
     * 
     * Finally, if you specify a configuration array as the second parameter of Solar::factory(), those values override both the default values of the class definition and the Solar.config.php values.
     * 
     * <code type="php">
     * 
     * $config = array('opt_2' => 'gir');
     * $example = Solar::factory('Example', $config);
     * 
     * // The values of $example->_config are now:
     * // 
     * // 'opt_1' => 'foo' ... as defined by the class
     * // 'opt_2' => 'gir' ... from the Solar::factory() instantiation config
     * // 'opt_3' => 'dib' ... from the config file
     * 
     * </code>
     * 
     * +++ Order of Precedence
     * 
     * All of this is to say that the order of precedence for
     * $_config property values looks like this:
     * 
     * * The values start as defined by the class,
     * 
     * * And are overwritten by any config file values,
     * 
     * * And are again overwritten by options set at instantiation time
     * 
     * Note that values not changed remain the same, so if you leave
     * one out, it's not overwritten to be null.
     * 
     * @param mixed $config If array, is merged with the default
     * $_config property array and any values from the
     * Solar.config.php file.  If string, is loaded from that file
     * and merged with values from Solar.config.php file.  If boolean
     * false, no config overrides are performed (class defaults
     * only).
     * 
     */
    public function __construct($config = null)
    {
        $class = get_class($this);
        
        if ($config === false) {
            // don't attempt to override class defaults at all,
            // usually for testing.
        } else {
            
            // normal behavior: merge from Solar.config.php,
            // then from construction-time config.
            
            // Solar.config.php values override class defaults
            $solar = Solar::config($class, null, array());
            $this->_config = array_merge($this->_config, $solar);
            
            // load construction-time config from a file?
            if (is_string($config)) {
                $config = Solar::run($config);
            }
            
            // construction-time values override Solar.config.php
            $this->_config = array_merge($this->_config, (array) $config);
        }
        
        // auto-define the locale directory if needed
        if (empty($this->_config['locale'])) {
            // converts "Solar_Test_Example" to
            // "Solar/Test/Example/Locale/"
            $this->_config['locale'] = str_replace('_', '/', $class);
            $this->_config['locale'] .= '/Locale/';
        }
        
        // load the locale strings
        $this->locale('');
    }
    
    /**
     * 
     * Reports the API version for this class.
     * 
     * If you don't override this method, your classes will use the same
     * API version string as the Solar package itself.
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
     * Looks up locale strings based on a key.
     * 
     * This is a convenience method that loads locale strings for the
     * class, and returns those strings based on the translation key.
     * Related reading includes [Main:LocaleFiles locale files] and
     * the [Solar_Locale:HomePage Solar_Locale class].
     * 
     * If you request a key that does not exist in the class-specific
     * locale file, or if there is no locale file for this class and
     * the current locale code, this method will fall back to the
     * system-wide Solar locale strings found in the
     * \\Solar/Locale/*\\ directory. If, after all that searching,
     * the key has no translation, this method will return the key
     * itself as the translation.
     * 
     * ++ Examples
     * 
     * +++ The Hard Way
     * 
     * The longhand way of doing localization, using only the Solar
     * arch-class, looks something like this:
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * // load the translation file for the 'Example' class
     * // based on the current locale code
     * Solar::registry('locale')->load('Example',
     * '/path/to/files/Locale/');
     * 
     * // get a translation for the ERR_EXAMPLE key
     * $string = Solar::locale('Example', 'ERR_EXAMPLE');
     * </code>
     * 
     * If you change locale codes, you need to re-load the strings:
     * 
     * <code type="php">
     * // change locale codes to Espanol
     * Solar::registry('locale')->setCode('es_ES');
     * 
     * // this string will be blank because the es_ES strings have not
     * // been loaded yet
     * $string = Solar::locale('Example', 'ERR_EXAMPLE');
     * 
     * // need to reload strings for the current locale
     * Solar::registry('locale')->load('Example',
     * '/path/to/files/Locale/');
     * 
     * // now we'll get a translation for the ERR_EXAMPLE key
     * $string = Solar::locale('Example', 'ERR_EXAMPLE');
     * </code>
     * 
     * +++ The Easy Way
     * 
     * The Solar_Base::locale() method does all the above work for
     * you.
     * 
     * First, you need to have defined $_config['locale'] as the path
     * to your locale files.
     * 
     * <code type="php">
     * class Example extends Solar_Base {
     *    protected $_config = array(
     *        'locale' => '/path/to/files/Locale/'
     *    );
     * }
     * </code>
     * 
     * Now you can use the Solar_Base::locale() method.
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * $example = Solar::factory('Example');
     * $string = $example->locale('ERR_EXAMPLE');
     * </code>
     * 
     * If you change locale codes, the method will automatically
     * reload strings for the new code on your next call to locale().
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * $example = Solar::factory('Example');
     * 
     * // get the default translation
     * $string = $example->locale('ERR_EXAMPLE');
     * 
     * // change the code and get another translation
     * Solar::registry('locale')->setCode('es_ES');
     * $string = $example->locale('ERR_EXAMPLE');
     * </code>
     * 
     * Finally, if the requested key does not exist in the
     * class-specific locale file, this method will "fall back" to
     * the all-purpose Solar locale file, generally located in
     * \\Solar/Locale/*\\, and look for the translation key there.
     * 
     * +++ Singular/Plural
     * 
     * The call to locale() takes an optional second parameter
     * indicating a number to associate with the translation.  If the
     * number is 1, a singular version of the translation will be
     * returned; if the number is more or less than exactly 1, a
     * plural version of the translation (if it exists) will be
     * returned.  See more on defining plurals in the
     * [Main:LocaleFiles locale files] documentation.
     * 
     * <code type="php">
     * require_once 'Solar.php';
     * Solar::start();
     * 
     * $example = Solar::factory('Example');
     * 
     * // get singular translations
     * $string = $example->locale('ERR_EXAMPLE');
     * $string = $example->locale('ERR_EXAMPLE', 1);
     * 
     * // get plural translations
     * $string = $example->locale('ERR_EXAMPLE', 0);
     * $string = $example->locale('ERR_EXAMPLE', 0.5);
     * $string = $example->locale('ERR_EXAMPLE', 1.1);
     * $string = $example->locale('ERR_EXAMPLE', 999);
     * </code>
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     * @todo rewrite docs
     * 
     */
    public function locale($key, $num = 1)
    {
        // is a locale directory specified?
        if (empty($this->_config['locale'])) {
            // use the generic Solar locale strings
            return Solar::locale('Solar', $key, $num);
        }
        
        // the name of this class
        $class = get_class($this);
        
        // do we need to load locale strings? we check for loading here
        // because the locale may have changed during runtime.
        if (! array_key_exists($class, Solar::$locale)) {
            // load the base Solar locale strings
            $dir = Solar::fixdir($this->_config['locale']);
            $file = $dir . Solar::getLocale() . '.php';
            
            // can we find the file?
            if (Solar::fileExists($file)) {
                Solar::$locale[$class] = (array) include $file;
            } else {
                // could not find file.
                // fail silently, as it's often the case that the
                // translation file simply doesn't exist.
                Solar::$locale[$class] = array();
            }
        }
        
        // get a translation for the current class
        $string = Solar::locale($class, $key, $num);
        
        // is the translation the same as the key?
        if ($string != $key) {
            // found a translation (i.e., different from the key)
            return $string;
        } else {
            // no translation found.
            // fall back to the generic Solar locale strings.
            return Solar::locale('Solar', $key, $num);
        }
    }
    
    
    /**
     * 
     * Sets a "read-once" session value for this class and a key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function setFlash($key, $val)
    {
        return Solar::setFlash(get_class($this), $key, $val);
    }
    
    /**
     * 
     * Appends a "read-once" session value for this class and key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public function addFlash($key, $val)
    {
        return Solar::addFlash(get_class($this), $key, $val);
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
    public function getFlash($key, $val = null)
    {
        return Solar::getFlash(get_class($this), $key, $val);
    }
    
    /**
     * 
     * Convenience method for returning exceptions with localized text.
     * 
     * This method attempts to automatically load and throw exceptions
     * based on the error code, falling back to generic Solar exceptions
     * when no specific exception classes exist.  For example, if a
     * class named 'Solar_Example' throws an error code 'ERR_FILE_NOT_FOUND',
     * attempts will be made to find these exception classes in this order:
     * 
     * # Example_Exception_FileNotFound (class specific)
     * 
     * # Solar_Exception_FileNotFound (Solar specific)</li>
     * 
     * # Example_Exception (class generic)</li>
     * 
     * # Solar_Exception (Solar generic)</li>
     * 
     * The final fallback is always the Solar_Exception class.
     * 
     * @param string $code The error code; does additional duty as the
     * locale string key and the exception class name suffix.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @return object A Solar_Exception object.
     * 
     */
    protected function _exception($code, $info = array())
    {
        // exception configuration
        $config = array(
            'class' => get_class($this),
            'code'  => $code,
            'text'  => $this->locale($code),
            'info'  => $info,
        );
        
        // the base exception class for this class
        $base = get_class($this) . '_Exception';
        
        // drop 'ERR_' and 'EXCEPTION_' prefixes from the code
        // to get a suffix for the exception class
        $suffix = $code;
        if (substr($suffix, 0, 4) == 'ERR_') {
            $suffix = substr($suffix, 4);
        } elseif (substr($suffix, 0, 10) == 'EXCEPTION_') {
            $suffix = substr($suffix, 10);
        }
        
        // convert suffix to StudlyCaps
        $suffix = str_replace('_', ' ', $suffix);
        $suffix = ucwords(strtolower($suffix));
        $suffix = str_replace(' ', '', $suffix);
        
        // look for Class_Exception_StudlyCapsSuffix
        try {
            $obj = Solar::factory("{$base}_$suffix", $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // fall back to Solar_Exception_StudlyCapsSuffix
        try {
            $obj = Solar::factory("Solar_Exception_$suffix", $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // look for generic Class_Exception
        try {
            $obj = Solar::factory($base, $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // final fallback to generic Solar_Exception
        return Solar::factory('Solar_Exception', $config);
    }
}
?>