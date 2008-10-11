<?php
/**
 * 
 * Staic support methods for config information.
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
class Solar_Config
{
    /**
     * 
     * The loaded values from the config file.
     * 
     * @var array
     * 
     * @see load()
     * 
     */
    static public $store = array();
    
    /**
     * 
     * The config values built for a class, including inheritance from its
     * parent class configs.
     * 
     * @var array
     * 
     * @see setBuild()
     * 
     * @see getBuild()
     * 
     */
    static protected $_build = array();
    
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
    static public function get($group, $elem = null, $default = null)
    {
        // are we looking for a group or an element?
        if (is_null($elem)) {
            
            // looking for a group. if no default passed, set up an
            // empty array.
            if ($default === null) {
                $default = array();
            }
            
            // find the requested group.
            if (empty(Solar_Config::$store[$group])) {
                return $default;
            } else {
                return Solar_Config::$store[$group];
            }
            
        } else {
            
            // find the requested group and element.
            if (! isset(Solar_Config::$store[$group][$elem])) {
                return $default;
            } else {
                return Solar_Config::$store[$group][$elem];
            }
        }
    }
    
    /**
     * 
     * Loads the config values from the specified location.
     * 
     * @param mixed $spec A config specification.
     * 
     * @see fetch()
     * 
     * @return void
     * 
     */
    static public function load($spec)
    {
        Solar_Config::$store = Solar_Config::fetch($spec);
    }
    
    /**
     * 
     * Fetches config file values.
     * 
     * Note that this method is overloaded by the variable type of $spec ...
     * 
     * * `null|false` (or empty) -- This will not load any new configuration
     *   values; you will get only the default [[Solar_Config::$store]] array values
     *   defined in the Solar class.
     * 
     * * `string` -- The string is treated as a path to a Solar.config.php
     *   file; the return value from that file will be used for [[Solar_Config::$store]].
     * 
     * * `array` -- This will use the passed array for the [[Solar_Config::$store]]
     *   values.
     * 
     * * `object` -- The passed object will be cast as an array, and those
     *   values will be used for [[Solar_Config::$store]].
     * 
     * @param mixed $spec A config specification.
     * 
     * @return array A config array.
     * 
     */
    static public function fetch($spec = null)
    {
        // load the config file values.
        // use alternate config source if one is given.
        if (is_array($spec) || is_object($spec)) {
            $config = (array) $spec;
        } elseif (is_string($spec)) {
            // merge from array file return
            $config = (array) Solar_File::load($spec);
        } else {
            // no added config
            $config = array();
        }
        
        return $config;
    }
    
    /**
     * 
     * Sets the build config to retain for a class.
     * 
     * **DO NOT** use this unless you know what you're doing.  The only reason
     * this is here is for Solar_Base::_buildConfig() to use it.
     * 
     * @param string $class The class name.
     * 
     * @param array $config The config built for that class.
     * 
     * @return void
     * 
     */
    static public function setBuild($class, $config)
    {
        Solar_Config::$_build[$class] = (array) $config;
    }
    
    /**
     * 
     * Gets the retained build config for a class.
     * 
     * **DO NOT** use this unless you know what you're doing.  The only reason
     * this is here is for Solar_Base::_buildConfig() to use it.
     * 
     * @param string $class The class name to get the config build for.
     * 
     * @return mixed An array of retained config built for the class, or null
     * if there's no build for it.
     * 
     */
    static public function getBuild($class)
    {
        if (array_key_exists($class, Solar_Config::$_build)) {
            return Solar_Config::$_build[$class];
        }
    }
}