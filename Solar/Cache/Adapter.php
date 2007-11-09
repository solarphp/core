<?php
/**
 * 
 * Abstract cache adapter.
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
abstract class Solar_Cache_Adapter extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Config keys are ...
     * 
     * `life`
     * : (int) The lifetime of each cache entry in seconds.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter = array(
        'active' => true,
        'life'   => 0,
    );
    
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
     * The lifetime of each cache entry in seconds.
     * 
     * @var int
     * 
     */
    protected $_life = 0;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct($config);
        
        // keep the cache active flag
        $this->_active = (bool) $this->_config['active'];
        
        // keep the cache lifetime value
        $this->_life = (int) $this->_config['life'];
    }
    
    /**
     * 
     * Makes the cache active (true) or inactive (false).
     * 
     * {{code: php
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // turn the cache off
     *     $cache->setActive(false);
     *     
     *     // turn it back on
     *     $cache->setActive(true);
     * }}
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
     * {{code: php
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // is the cache active or not?
     *     $flag = $cache->isActive();
     *     Solar::dump($flag);
     * }}
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
     * Gets the cache lifetime in seconds.
     * 
     * @return int The cache lifetime in seconds.
     * 
     */
    public function getLife()
    {
        return $this->_life;
    }
    
    /**
     * 
     * Updates cache entry data, inserting if it does not already exist.
     * 
     * This method stores data to the cache with its own entry
     * identifier.  If the entry does not exist, it is created; if
     * the entry does already exist, it is updated with the new data.
     * 
     * Does not replace if caching is not active.
     * 
     * For example, to store an array in the cache ...
     * 
     * {{code: php
     *     // create a cache object
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // create a unique ID
     *     $id = 'my_array';
     *     
     *     // set up some data to cache (this could be string output, or
     *     // an object, or almost anything else)
     *     $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
     *     
     *     // store to the cache, overwriting any previous $id entry
     *     $cache->save($id, $data);
     *     
     *     // now fetch back the data for the $id entry
     *     $result = $cache->fetch($id);
     *     
     *     // $data and $result should be identical
     * }}
     * 
     * @param string $key The entry ID.
     * 
     * @param string $data The data to store.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    abstract public function save($key, $data);
    
    /**
     * 
     * Inserts cache entry data *only if it does not already exist*.
     * 
     * Useful for avoiding race conditions when one or more processes may be
     * writing to the same entry.
     * 
     * This method will not update an existing entry; if the entry already
     * exists, this method will not replace it.
     * 
     * For example, to add an array in the cache ...
     * 
     * {{code: php
     *     // create a cache object
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // create a unique ID
     *     $id = 'my_array';
     *     
     *     // set up some data to cache (this could be string output, or
     *     // an object, or almost anything else)
     *     $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
     *     
     *     // store to the cache (fails if $id already exists)
     *     $cache->add($id, $data);
     *     
     *     // now fetch back the data for the $id entry
     *     $result = $cache->fetch($id);
     * }}
     * 
     * @param string $key The entry ID.
     * 
     * @param string $data The data to store.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    abstract public function add($key, $data);
    
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
     * name, you could do this ...
     * 
     * {{code: php
     *     // create a request object
     *     $request = Solar_Registry::get('request');
     *     
     *     // get the request URI as an identifier
     *     $id = $request->server('REQUEST_URI');
     *     
     *     // create a cache object
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // fetch the result and dump it to screen
     *     $result = $cache->fetch($id);
     *     Solar::dump($result);
     * }}
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    abstract public function fetch($key);
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * {{code: php
     *     // create a request object
     *     $request = Solar_Registry::get('request');
     *     
     *     // create an entry ID named for the current URI
     *     $id = $request->server('REQUEST_URI');
     *     
     *     // create a cache object
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // delete any cache entry with that ID
     *     $cache->delete($id);
     * }}
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    abstract public function delete($key);
    
    /**
     * 
     * Deletes all entries from the cache.
     * 
     * {{code: php
     *     $cache = Solar::factory('Solar_Cache');
     *     $cache->deleteAll();
     * }}
     * 
     * @return void
     * 
     */
    abstract public function deleteAll();
        
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
     * {{code: php
     *     // create a request object
     *     $request = Solar_Registry::get('request');
     *     
     *     // create an entry ID named for the current URI
     *     $id = $request->server('REQUEST_URI');
     *     
     *     // create a cache object
     *     $cache = Solar::factory('Solar_Cache');
     *     
     *     // find out what the underlying cache adapter uses as the entry name
     *     $real_name = $cache->entry($id);
     * }}
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed The adapter-specific name for the entry key.
     * 
     */
    abstract public function entry($key);
} 

