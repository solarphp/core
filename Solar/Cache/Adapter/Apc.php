<?php
/**
 * 
 * APC cache controller.
 * 
 * Requires APC 3.0.13 or later.
 * 
 * The Alternative PHP Cache (APC) is a free and open opcode cache for PHP.
 * It was conceived of to provide a free, open, and robust framework for
 * caching and optimizing PHP intermediate code.
 * 
 * The APC extension is not bundled with PHP; you will need to install it
 * on your server before you can use it. You can read more about it at the
 * [APC homepage](http://pecl.php.net/package/apc).
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Cache_Adapter_Apc extends Solar_Cache_Adapter
{
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // make sure we have apc available
        if (! ( extension_loaded('apc') && ini_get('apc.enabled') ) ) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'apc')
            );
        }
        
        // we're ok
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
        
        return apc_store($key, $data, $this->_life);
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
        
        return apc_add($key, $data, $this->_life);
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
        
        return apc_fetch($key);
    }
    
    /**
     * 
     * Increments a cache entry value by the specified amount.  If the entry
     * does not exist, creates it at zero, then increments it.
     * 
     * @param string $key The entry ID.
     * 
     * @param string $amt The amount to increment by (default +1).  Using
     * negative values is effectively a decrement.
     * 
     * @return int The new value of the cache entry.
     * 
     */
    public function increment($key, $amt = 1)
    {
        if (! $this->_active) {
            return;
        }
        
        // make sure we have a key to increment
        $this->add($key, 0, null, $this->_life);
        
        // fetch the current value
        $val = $this->fetch($key);
        
        // increment and save
        $val += $amt;
        $this->save($key, $val);
        
        // re-fetch in case someone else incremented in the interim
        $val = $this->fetch($key);
        
        // done
        return $val;
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
        
        apc_delete($key);
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
        
        apc_clear_cache('user');
    }
}
