<?php
/**
 * 
 * eAccellerator cache controller.
 * 
 * eAccelerator is a free open-source PHP accelerator, optimizer,
 * encoder and dynamic content cache. It increases the performance of
 * PHP scripts by caching them in their compiled state, so that the
 * overhead of compiling is almost completely eliminated.
 * 
 * eAccelerator is not bundled with PHP; you will need to install it
 * on your server before you can use it.  More info on the
 * [eAccelerator homepage](http://eaccelerator.net/).
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
 * @todo Does not work with objects.  Need to add custom support for them.
 * <http://bart.eaccelerator.net/doc/phpdoc/eAccelerator/_shared_memory_php.html#functioneaccelerator_put>
 * 
 */
class Solar_Cache_Adapter_Eaccelerator extends Solar_Cache_Adapter
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
        if (! (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'eaccelerator')
            );
        }
        
        // we're ok
        parent::__construct($config);
    }
    
    /**
     * 
     * Sets cache entry data. eAccelerator doesn't serialize object, so
     * you need to do it yourself or php will segfault on object retrieval.
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
        
        return eaccelerator_put($key, $data, $this->_life);
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
        
        if (eaccelerator_get($key) !== null) {
            return false;
        }
        
        return eaccelerator_put($key, $data, $this->_life);
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        return eaccelerator_get($key);
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
        
        eaccelerator_rm($key);
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if (! $this->_active) {
            return;
        }
        
        eaccelerator_clean();
    }
}
