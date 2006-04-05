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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
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
     * Default config keys are:
     * 
     * : \\locale\\ : (string) Directory where locale files for the class are kept
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
        return '0.15.1';
    }
    
    /**
     * 
     * Looks up locale strings based on a key.
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
     * # Solar_Exception_FileNotFound (Solar specific)
     * 
     * # Example_Exception (class generic)
     * 
     * # Solar_Exception (Solar generic)
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