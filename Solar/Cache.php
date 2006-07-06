<?php
/**
 * 
 * Facade class for cache adapters.
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

/**
 * 
 * Facade class for cache adapters.
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
     * : \\active\\ : (bool) Whether the cache is active or not when instantiated.
     * 
     * : \\adapter\\ : (string) The adapter class, default 'Solar_Cache_Adapter_File'.
     * 
     * : \\config\\ : (array) Construction-time config keys to pass to the adapter
     *   to override Solar.config.php values.  Default is null.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache = array(
        'active'  => true,
        'adapter' => 'Solar_Cache_Adapter_File',
        'config'  => null,
    );
    
    /**
     * 
     * The instantiated adapter object.
     * 
     * @var object
     * 
     */
    protected $_adapter;
    
    /**
     * 
     * Whether or not the cache is active.
     * 
     * @var bool
     * 
     */
    protected $_active;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // are we active?
        $this->_active = (bool) $this->_config['active'];
        
        // instantiate an adapter object
        $this->_adapter = Solar::factory(
            $this->_config['adapter'],
            $this->_config['config']
        );
    }
    
    /**
     * 
     * Makes the cache active (true) or inactive (false).
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // turn the cache off
     * $cache->setActive(false);
     * 
     * // turn it back on
     * $cache->setActive(true);
     * </code>
     * 
     * @param bool $flag True to turn on, false to turn off.
     * 
     * @return void
     * 
     */
    public function setActive($flag)
    {
        $this->_active = (bool) $flag;
    }
    
    /**
     * 
     * Gets the current activity state of the cache (on or off).
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // is the cache active or not?
     * $flag = $cache->isActive();
     * Solar::dump($flag);
     * </code>
     * 
     * @return bool True if active, false if not.
     * 
     */
    public function isActive()
    {
        return $this->_active;
    }
    
    /**
     * 
     * Returns the cache lifetime in seconds.
     * 
     * @return int The cache lifetime in seconds.
     * 
     */
    public function getLife()
    {
        return $this->_adapter->getLife();
    }
    
    
    /**
     * 
     * Inserts/updates cache entry data.
     * 
     * This method stores data to the cache with its own entry
     * identifier.  If the entry does not exist, it is created; if
     * the entry does already exist, it is updated with the new data.
     * 
     * Does not replace if caching is not active.
     * 
     * For example, to store an array in the cache ...
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create a unique ID
     * $id = 'my_array';
     * 
     * // set up some data to cache (this could be string output, or
     * // an object, or almost anything else)
     * $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
     * 
     * // store to the cache, overwriting any previous $id entry
     * $cache->save($id, $data);
     * 
     * // now fetch back the data for the $id entry
     * $result = $cache->fetch($id);
     * 
     * // $data and $result should be identical
     * </code>
     * 
     * @param string $key The entry ID.
     * 
     * @param string $data The data to store.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function save($key, $data)
    {
        if ($this->isActive()) {
            return $this->_adapter->save($key, $data);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * Use this to retrieve the cache entry identifed by key.  The
     * key can be any scalar value:  a web page name, an integer ID,
     * a simple name, and so on.
     * 
     * If the cache entry does not exist, or if it has passed its
     * lifetime (defined in the adapter's config keys), the
     * function will return boolean false; otherwise, it will return
     * the contents of the cache entry.
     * 
     * For example, to get a cache entry identified by a web page
     * name, you could do this:
     * 
     * <code type="php">
     * // create a cache object
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // get the request URI as an identifier
     * $id = Solar::server('REQUEST_URI');
     * 
     * // fetch the result and dump it to screen
     * $result = $cache->fetch($id);
     * Solar::dump($result);
     * </code>
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    public function fetch($key)
    {
        if ($this->isActive()) {
            return $this->_adapter->fetch($key);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create an entry ID named for the current URI
     * $id = Solar::server('REQUEST_URI');
     * 
     * // delete any cache entry with that ID
     * $cache->delete($id);
     * </code>
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        if ($this->isActive()) {
            return $this->_adapter->delete($key);
        }
    }
    
    /**
     * 
     * Deletes all entries from the cache.
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // delete all entries
     * $cache->deleteAll();
     * </code>
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if ($this->isActive()) {
            return $this->_adapter->deleteAll();
        }
    }
    
    /**
     * 
     * Returns the adapter-specific name for the entry key.
     * 
     * Cache adapters do not always use the identifier you specify for
     * cache entries.  For example, the [Solar_Cache_File:HomePage file adapter]
     * names the cache entries based on an MD5 hash of the entry ID. 
     * This method tells you what the adapter is using as the name for
     * the cache entry.
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create an entry ID named for the current URI
     * $id = Solar::server('REQUEST_URI');
     * 
     * // find out what the underlying cache adapter uses as the entry name
     * $real_name = $cache->entry($id);
     * </code>
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed The adapter-specific name for the entry key.
     * 
     */
    public function entry($key)
    {
        return $this->_adapter->entry($key);
    }
}
?>