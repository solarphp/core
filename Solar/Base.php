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
 * extended.  Solar_Base is relatively light, and provides ...
 * 
 * * Construction-time reading of [Main:ConfigFile config file] options 
 *   for itself, and merging of those options with any options passed   
 *   for instantation, along with the class-defined config defaults,  
 *   into the Solar_Base::$_config property.
 * 
 * * A Solar_Base::locale() convenience method to return locale strings.
 * 
 * * A Solar_Base::_exception() convenience method to generate
 *   exception objects with translated strings from the locale file
 * 
 * Note that you do not define config defaults in $_config directly; 
 * instead, you use a protected property named for the class, with an
 * underscore prefix.  For exmple, a "Vendor_Class_Name" class would 
 * define the default config array in "$_Vendor_Class_Name".  This 
 * convention lets child classes inherit parent config keys and values.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
abstract class Solar_Base {
    
    /**
     * 
     * Collection point for configuration values.
     * 
     * Note that you do not define config defaults in $_config directly; 
     * instead, you use a protected property named for the class, with 
     * an underscore prefix.
     * 
     * For exmple, a "Vendor_Class_Name" class would define the default 
     * config array in "$_Vendor_Class_Name".  This convention lets 
     * child classes inherit parent config keys and values.
     * 
     * @var array
     * 
     */
    protected $_config = array();
    
    /**
     * 
     * Constructor.
     * 
     * If the $config param is an array, it is merged with the class
     * config array and any values from the Solar.config.php file.
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
        if (empty(Solar::$config_base[$class])) {
            // merge from config file
            $parents = array_reverse(Solar::parents($this, true));
            foreach ($parents as $class) {
                $var = "_$class";
                $prop = empty($this->$var) ? null : $this->$var;
                $this->_config = array_merge(
                    // current values
                    $this->_config,
                    // override with class property config
                    (array) $prop,
                    // override with solar config for the class
                    Solar::config($class, null, array())
                );
            }
            Solar::$config_base[$class] = $this->_config;
        } else {
            $this->_config = Solar::$config_base[$class];
        }
        
        // final override with construct-time config
        $this->_config = array_merge($this->_config, (array) $config);
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
        return Solar::locale(get_class($this), $key, $num);
    }
    
    /**
     * 
     * Convenience method for returning exceptions with localized text.
     * 
     * @param string $code The error code; does additional duty as the
     * locale string key and the exception class name suffix.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @return Solar_Exception An instanceof Solar_Exception.
     * 
     */
    protected function _exception($code, $info = array())
    {
        $class = get_class($this);
        return Solar::exception(
            $class,
            $code,
            Solar::locale($class, $code),
            (array) $info
        );
    }
}
?>