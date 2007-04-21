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
 * 
 * Memcache cache controller.
 * 
 * This adapter lets you connect to a
 * [memcached](http://www.danga.com/memcached/) server, which uses system
 * memory to cache data. In general, you never need to instantiate it 
 * yourself; instead, use Solar_Cache as the frontend for it and specify
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
     * `timeout`
     * : (int) The timeout before the server connection is
     *   considered a miss, in seconds.  Default is 1 second, and should 
     *   not really be changed for reasons other than testing purposes.
     * 
     * `pool`
     * : (array) An array of memcache connections to connect to in a 
     *   multi-server pool. Each connection should be represented by an array
     *   with the following keys: `host`, `port`, `persistent`, `weight`, 
     *   `timeout`, `retry_interval`, `status` and `failure_callback`.
     *   The `pool` is empty by default, and will only be used instead of 
     *   a single-server connection if non-empty.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_Memcache = array(
        'host' => 'localhost',
        'port' => 11211,
        'timeout' => 1,
        'pool' => array(),
    );
    
    /**
     * 
     * Default configuration for a pool server node.
     * 
     * Keys are ...
     * 
     * `host`
     * : (string) The memcached host name, default 'localhost'.
     * 
     * `port`
     * : (int) The memcached port number, default 11211.
     * 
     * `persistent`
     * : (bool) Controls the use of a persistent connection, default **TRUE**.
     * 
     * `weight`
     * : (int) Number of buckets to create for this server, which in turn 
     *   controls its probability of being selected. The probability is 
     *   relative to the total weight of all servers. Default 1.
     * 
     * `timeout`
     * : (int) Value in seconds which will be used for connecting to the 
     *   daemon. Default 1.
     * 
     * `retry_interval`
     * : (int) Controls how often a failed server will be retried. Default is
     *   15 seconds. A setting of -1 disables automatic retry.
     * 
     * `status`
     * : (bool) Controls if the server should be flagged as online. Setting 
     *   this parameter to **FALSE** and `retry_interval` to -1 allows a failed
     *   server to be kept in the pool so as not to affect the key distribution
     *   algorithm. Requests for this server will then failover or fail
     *   immediately depending on the *memcache.allow_failover* php.ini setting.
     *   Defaults to **TRUE**, meaning the server should be considered online.
     * 
     * `failure_callback`
     * : (callback) Allows specification of a callback function to run upon
     *   encountering a connection error. The callback is run before 
     *   failover is attempted, and takes two parameters: the hostname and port
     *   of the failed server. Default is null.
     * 
     * @var array
     * 
     */
    protected $_pool_node = array(
        'host'              => 'localhost',
        'port'              => 11211,
        'persistent'        => true,
        'weight'            => 1,
        'timeout'           => 1,
        'retry_interval'    => 1,
        'status'            => true,
        'faliure_callback'  => null,        
    );
    
    
    /**
     * 
     * A memcache client object.
     * 
     * @var object
     * 
     */
    public $memcache;
    
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
        
        // construction
        parent::__construct($config);
        $this->memcache = new Memcache;
        
        // pool or single-server connection?
        if (empty($this->_config['pool'])) {
            
            // make sure we can connect
            $result = @$this->memcache->connect(
                $this->_config['host'],
                $this->_config['port'],
                $this->_config['timeout']
            );
        
            if (! $result) {
                throw $this->_exception(
                    'ERR_CONNECTION_FAILED',
                    array(
                        'host' => $this->_config['host'],
                        'port' => $this->_config['port'],
                        'timeout' => $this->_config['timeout'],
                    )
                );
            }
            
        } else {
            
            // set up a pool
            $this->_createPool();
        }
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
        
        return $this->memcache->set($key, $data, null, $this->_life);
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
        
        return $this->memcache->get($key);
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
        
        $this->memcache->delete($key);
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
        
        $this->memcache->flush();
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
    
    /**
     * 
     * Adds servers to a memcache connection pool from configuration.
     * 
     * @return void
     * 
     */
    protected function _createPool()
    {
        $connection_count = 0;
        
        foreach ($this->_config['pool'] as $server) {
            // set all defaults
            $server = array_merge($this->_pool_node, $server);
            
            // separate addServer calls in case failure_callback is 
            // empty
            if (empty($server['failure_callback'])) {
                $result = $this->memcache->addServer(
                    (string) $server['host'],
                    (int)    $server['port'],
                    (bool)   $server['persistent'],
                    (int)    $server['weight'],
                    (int)    $server['retry_interval'],
                    (bool)   $server['status']
                );
                                
            } else {
                $result = $this->memcache->addServer(
                    (string) $server['host'],
                    (int)    $server['port'],
                    (bool)   $server['persistent'],
                    (int)    $server['weight'],
                    (int)    $server['retry_interval'],
                    (bool)   $server['status'],
                             $server['failure_callback']
                );
            }
            
            // Did connection to the last node succeed?
            if ($result === true) {
                $connection_count++;
            }

        }
        
        // make sure we connected to at least one
        if ($connection_count < 1) {
            throw $this->_exception(
                'ERR_CONNECTION_FAILED',
                $this->_config['pool']
            );
        }
        
    }

}
