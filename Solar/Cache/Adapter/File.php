<?php
/**
 * 
 * File-based cache controller.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 1890 2006-10-19 22:00:24Z pmjones $
 * 
 */

/**
 * The abstract cache adapter.
 */
Solar::loadClass('Solar_Cache_Adapter');

/**
 * 
 * File-based cache controller.
 * 
 * This is the file-based adapter for [Solar_Cache:HomePage Solar_Cache].
 * In general, you never need to instantiate it yourself; instead,
 * use Solar_Cache as the frontend for it and specify
 * 'Solar_Cache_File' as the 'adapter' config key value.
 * 
 * If you specify a path (for storing cache entry files) that does
 * not exist, this adapter attempts to create it for you.
 * 
 * This adapter always uses [[php::flock() | ]] when reading and writing
 * cache entries; it uses a shared lock for reading, and an exclusive
 * lock for writing.  This is to help cut down on cache corruption
 * when two processes are trying to access the same cache file entry,
 * one for reading and one for writing.
 * 
 * In addition, this adapter automatically serializes and unserializes
 * arrays and objects that are stored in the cache.  This means you
 * can store not only string output, but also array data and entire
 * objects in the cache ... just like Solar_Cache_Memcache.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @todo Add CRC32 to check for cache corruption?
 * 
 */
class Solar_Cache_Adapter_File extends Solar_Cache_Adapter {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Config keys are ...
     * 
     * `path`
     * : (string) The directory where cache files are located; 
     *   should be readable and writable by the script process, usually
     *   the web server process. Default is '/Solar_Cache_File' in the system
     *   temporary directory.
     * 
     * `life`
     * : (int) The lifetime of each cache entry in seconds;
     *   default is 3600 seconds (i.e., 1 hour).
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_File = array(
        'path'   => null, // default set in constructor
        'life'   => 3600
    );
    
    /**
     * 
     * Path to the cache directory.
     * 
     * @var string
     * 
     */
    protected $_path;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // set the default cache directory location
        $this->_Solar_Cache_Adapter_File['path'] == Solar::temp('/Solar_Cache_File/');
        
        // basic construction
        parent::__construct($config);
        
        // keep local values so they can't be changed
        $this->_path = Solar::fixdir($this->_config['path']);
        
        // make sure the cache directory is there; create it if
        // necessary.
        if (! is_dir($this->_path)) {
            mkdir($this->_path, 0777, true);
        }
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
    public function fetch($key)
    {
        // get the entry filename *before* validating;
        // this avoids race conditions.
        $file = $this->entry($key);
        
        // make sure the file exists and is readable,
        if (file_exists($file) && is_readable($file)) {
            // has the file expired?
            $expire_time = filemtime($file) + $this->_config['life'];
            if (time() > $expire_time) {
                // expired, remove it
                $this->delete($key);
                return false;
            }
        }
        
        // file exists; open it for reading
        $fp = @fopen($file, 'rb');
        
        // could it be opened?
        if ($fp) {
        
            // lock the file right away
            flock($fp, LOCK_SH);
            
            // get the cache entry data.
            // PHP caches file lengths; clear that out so we get
            // an accurate file length.
            clearstatcache();
            $len = filesize($file);
            $data = fread($fp, $len);
            
            // check for serializing while file is locked
            // to avoid race conditions
            if (file_exists($file . '.serial')) {
                $data = unserialize($data);
            }
            
            // unlock and close the file
            flock($fp, LOCK_UN);
            fclose($fp);
            
            // done!
            return $data;
        }
        
        // could not open file.
        return false;
    }
    
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
    public function save($key, $data)
    {
        // should the data be serialized?
        if (is_array($data) || is_object($data)) {
            $data = serialize($data);
            $serial = true;
        } else {
            $serial = false;
        }
        
        // open the file for over-writing
        $file = $this->entry($key);
        $fp = @fopen($file, 'wb');
        
        // was it opened?
        if ($fp) {
            
            // yes.  exclusive lock for writing.
            flock($fp, LOCK_EX);
            fwrite($fp, $data, strlen($data));
            
            // add a .serial file? (do this while the file
            // is locked to avoid race conditions)
            if ($serial) {
                touch($file . '.serial');
            } else {
                // make sure no serial file is there
                // from a previous entry with the same
                // name
                @unlink($file . '.serial');
            }
            
            // unlock and close, then done.
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
        
        // could not open the file for writing.
        return false;
    }
    
    /**
     * 
     * Deletes an entry from the cache.
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        $file = $this->entry($key);
        unlink($file);
        @unlink($file . '.serial');
    }
    
    /**
     * 
     * Removes all entries from the cache.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        // open the directory
        $dir = dir($this->_path);
        
        // did it exist?
        if ($dir) {
            
            // delete each file in the cache directory.
            // we use the "false !==" piece so that a file
            // named '0' does not prematurely terminate the
            // loop.
            while (false !== ($file = $dir->read())) {
                // delete the file (suppress errors so that . and ..
                // don't throw warnings) ...
                @unlink($this->_path . $file);
                // ... and any serial marker.
                @unlink($file . '.serial');
            }
            
            // done
            $dir->close();
        }
    }
    
    /**
     * 
     * Returns the path and filename for the entry key.
     * 
     * @param string $key The entry ID.
     * 
     * @return string The cache entry path and filename.
     * 
     */
    public function entry($key)
    {
        return $this->_path . md5($key);
    }
} 

?>