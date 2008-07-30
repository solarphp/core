<?php
class Solar_Config
{
    /**
     * 
     * The loaded values.
     * 
     * @var array
     * 
     * @see load()
     * 
     */
    static public $store = array();
    
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
            if (empty(self::$store[$group])) {
                return $default;
            } else {
                return self::$store[$group];
            }
            
        } else {
            
            // find the requested group and element.
            if (! isset(self::$store[$group][$elem])) {
                return $default;
            } else {
                return self::$store[$group][$elem];
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
        self::$store = self::fetch($spec);
    }
    
    /**
     * 
     * Fetches config file values.
     * 
     * Note that this method is overloaded by the variable type of $spec ...
     * 
     * * `null|false` (or empty) -- This will not load any new configuration
     *   values; you will get only the default [[self::$store]] array values
     *   defined in the Solar class.
     * 
     * * `string` -- The string is treated as a path to a Solar.config.php
     *   file; the return value from that file will be used for [[self::$store]].
     * 
     * * `array` -- This will use the passed array for the [[self::$store]]
     *   values.
     * 
     * * `object` -- The passed object will be cast as an array, and those
     *   values will be used for [[self::$store]].
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
    
}