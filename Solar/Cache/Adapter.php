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

/**
 * 
 * Abstract cache adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
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
        'life'   => 0,
    );
    
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
        
        // keep the cache lifetime value
        if (empty($this->_config['life'])) {
            $this->_life = 0;
        } else {
            $this->_life = (int) $this->_config['life'];
        }
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
     * Fetches cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    abstract public function fetch($key);
    
    /**
     * 
     * Inserts/updates cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    abstract public function save($key, $data);
    
    /**
     * 
     * Deletes an entry from the cache.
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    abstract public function delete($key);
    
    /**
     * 
     * Removes all entries from the cache.
     * 
     * @return void
     * 
     */
    abstract public function deleteAll();
        
    /**
     * 
     * Returns the adapter-specific ID for the entry ID.
     * 
     * @param string $key The entry ID.
     * 
     * @return string The cache entry path and filename.
     * 
     */
    abstract public function entry($key);
} 

?>