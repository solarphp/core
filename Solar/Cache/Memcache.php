<?php
/**
 * 
 * Memcache cache controller.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Cache
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
 * Memcache cache controller.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Cache
 * 
 */
class Solar_Cache_Memcache extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * host => (string) the memcache server hostname
     * 
     * port => (string|int) the port on the server
     * 
     * life => (int) lifetime in seconds for each cache entry
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'host' => 'localhost',
        'port' => '11211',
        'life' => 60,
    );
    
    /**
     * 
     * A memcache client object.
     * 
     * @access protected
     * 
     * @var object
     * 
     */
    protected $_memcache;
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     * @param array $config An array of user-supplied configuration
     * values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_config['life'] = (int) $this->_config['life'];
        $this->_memcache = new Memcache;
        $this->_memcache->connect($this->_config['host'], $this->_config['port']);
    }
    
    /**
     * 
     * Sets cache entry data.
     * 
     * @access public
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function replace($key, $data)
    {
        return $this->_memcache->set($key, $data, null, $this->_config['life']);
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
        return $this->_memcache->get($key);
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
        $this->_memcache->delete($key);
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * @access public
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
     * @access public
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