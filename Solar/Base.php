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
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
 * * A Solar_Base::_log() convenience method to save log messages
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
     * @var array
     * 
     */
    protected $_config = array(
    );
    
    /**
     * 
     * Constructor.
     * 
     * If the $config param is an array, it is merged with the default
     * $_config property array and any values from the Solar.config.php
     * file.
     * 
     * If the $config param is a string, config is loaded from that file
     * and merged with values from Solar.config.php file.
     * 
     * If the $config param is boolean false, no config overrides are
     * performed (class defaults only).
     * 
     * The Solar.config.php values are inherited along class parent
     * lines; e.g., all classes descending from Solar_Base use the 
     * Solar_Base config file values until overridden.
     * 
     * @param mixed $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $class = get_class($this);
        
        // only process configs if construction-time config is
        // non-false.
        if ($config !== false) {
            
            // load construction-time config from a file?
            if (is_string($config)) {
                $config = Solar::run($config);
            }
        
            // get the parents of this class, including this class
            $stack = Solar::parents($class, true);
            
            // Merge from config file.
            // Parent-class config file values are inherited.
            foreach ($stack as $class) {
                $solar = Solar::config($class, null, array());
                $this->_config = array_merge($this->_config, $solar);
            }
            
            // construction-time values override config file values.
            $this->_config = array_merge($this->_config, (array) $config);
        }
        
        // get the log object if one was specified
        if (! empty($this->_config['log'])) {
            $this->_log = Solar::dependency('Solar_Log', $this->_log);
        }
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
        $class = get_class($this);
        return Solar::locale($class, $key, $num);
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