<?php
/**
 * 
 * Memcache cache controller.
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
 * The abstract cache adapter.
 */
Solar::loadClass('Solar_Cache_Adapter');

/**
 * 
 * Memcache cache controller.
 * 
 * This adapter lets you connect to a [http://www.danga.com/memcached/
 * memcached] server, which uses system memory to cache data.  In
 * general, you never need to instantiate it yourself; instead, use
 * Solar_Cache as the frontend for it and specify
 * 'Solar_Cache_Memcache' in the config keys as the 'adapter' value.
 * 
 * This kind of cache is extremely fast, especially when on the same
 * server as the web process, although it may also be accessed via
 * network.  This particular adapter uses the PHP [[php::memcache | ]]
 * extension to manage the cache connection.  The extension is not
 * bundled with PHP; you will need to follow the
 * [installation instructions](http://php.net/memcache) before you can
 * use it.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Cache_Adapter_Memcache extends Solar_Cache_Adapter {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `host`
     * : (string) The memcached host name, default 'localhost'.
     * 
     * `port`
     * : (int) The memcached port number, default 11211.
     * 
     * `life`
     * : (int) The cache entry lifetime in seconds, default 60.
     * 
     * `timeout`
     * : (int) The timeout before the server connection is
     *   considered a miss, in seconds.  Default is 1 second, and should 
     *   not really be changed for reasons other than testing purposes.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_Memcache = array(
        'host' => 'localhost',
        'port' => 11211,
        'life' => 60,
        'timeout' => 1,
    );
    
    /**
     * 
     * A memcache client object.
     * 
     * @var object
     * 
     */
    protected $_memcache;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // make sure we have memcache available
        if (! extension_loaded('memcache')) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'memcache')
            );
        }
        
        // we're ok
        parent::__construct($config);
        $this->_memcache = new Memcache;
        $this->_memcache->connect(
            $this->_config['host'],
            $this->_config['port'],
            $this->_config['timeout']
        );
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
        return $this->_memcache->set($key, $data, null, $this->_life);
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
        return $this->_memcache->get($key);
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
        $this->_memcache->delete($key);
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
        $this->_memcache->flush();
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
?>