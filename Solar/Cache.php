<?php
/**
 * 
 * Class for cache control.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
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
 * Class for cache control.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Cache extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * active  => (bool) setting for $active
     * 
     * class  => (string) the cache driver to use
     * 
     * options => (array) array of config options for the driver
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'active'  => true,
        'class'   => 'Solar_Cache_File',
        'options' => null
    );
    
    /**
     * 
     * The instantiated driver object.
     * 
     * @access protected
     * 
     * @var object
     * 
     */
    protected $_driver;
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     * @param array $config An array of configuration options.
     * 
     */
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // instantiate a driver object
        $this->_driver = Solar::factory(
            $this->_config['class'],
            $this->_config['options']
        );
    }
    
    /**
     * 
     * Turns caching on and off.
     * 
     * @access public
     * 
     * @param bool $flag True to turn on, false to turn off.
     * 
     */
    public function setActive($flag)
    {
        $this->_config['active'] = (bool) $flag;
    }
    
    /**
     * 
     * Returns the current caching activity flag.
     * 
     * @access public
     * 
     * @return bool True if active, false if not.
     * 
     */
    public function active()
    {
        return $this->_config['active'];
    }
    
    /**
     * 
     * Inserts/updates cache entry data.
     * 
     * Does not replace if caching is not active.
     * 
     * @access public
     * 
     * @param string $key The entry ID.
     * 
     * @param string $data The data to store.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function replace($key, $data)
    {
        if ($this->active()) {
            return $this->_driver->replace($key, $data);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @access public
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    public function fetch($key)
    {
        if ($this->active()) {
            return $this->_driver->fetch($key);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * @access public
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        if ($this->active()) {
            return $this->_driver->delete($key);
        }
    }
    
    /**
     * 
     * Removes all entities from the cache.
     * 
     * @access public
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if ($this->active()) {
            return $this->_driver->deleteAll();
        }
    }
    
    /**
     * 
     * Returns the driver-specific name for the entry key.
     * 
     * @access public
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed The driver-specific name for the entry key.
     * 
     */
    public function entry($key)
    {
        return $this->_driver->entry($key);
    }
}
?>