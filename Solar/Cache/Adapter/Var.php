<?php
/**
 * 
 * Variable (in-memory) cache controller.
 * 
 * Stores cache entries to an object variable.  This means that entries are
 * available for the duration of the script, but are cleared out at the end
 * of the script.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Cache_Adapter_Var extends Solar_Cache_Adapter {
    
    /**
     * 
     * Cache entries.
     * 
     * @var array
     * 
     */
    protected $_entry = array();
    
    /**
     * 
     * Expiration timestamps for each cache entry.
     * 
     * @var array
     * 
     */
    protected $_expires = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * Sets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function save($key, $data)
    {
        if (! $this->_active) {
            return;
        }
        
        $this->_entry[$key] = $data;
        $this->_expires[$key] = time() + $this->_life;
        return true;
    }
    
    /**
     * 
     * Inserts cache entry data, but only if the entry does not already exist.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function add($key, $data)
    {
        if (! $this->_active) {
            return;
        }
        
        if (empty($this->_entry[$key])) {
            return $this->save($key, $data);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, cache data on success.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        if (! empty($this->_entry[$key]) && $this->_expires[$key] <= time()) {
            // exists, and is within its lifetime
            return $this->_entry[$key];
        } else {
            // clear the entry
            unset($this->_entry[$key]);
            unset($this->_expires[$key]);
            return false;
        }
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        if (! $this->_active) {
            return;
        }
        
        unset($this->_entry[$key]);
        unset($this->_expires[$key]);
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * Note that APC makes a distinction between "user" entries and
     * "system" entries; this only deletes the "user" entries.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if (! $this->_active) {
            return;
        }
        
        $this->_entry = array();
        $this->_expires = array();
    }
    
    /**
     * 
     * Returns the name for the entry key.
     * 
     * @param string $key The entry ID.
     * 
     * @return string The cache entry name.
     * 
     */
    public function entry($key)
    {
        return $key;
    }
}
