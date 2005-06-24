<?php

/**
* 
* File-based cache controller.
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
* File-based cache controller.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Cache
* 
* @todo Add CRC32 to check for cache corruption?
* 
*/

class Solar_Cache_File extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* path => (string) the directory path where cache files are stored
	* 
	* life => (int) lifetime in seconds for each cache entry
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'path'   => '/tmp/Solar_Cache_File/',
		'life'   => 3600
	);
	
	
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
		// basic construction
		parent::__construct($config);
		
		// keep local values so they can't be changed
		$this->config['path'] = Solar::fixdir($this->config['path']);
		$this->config['life'] = (int) $this->config['life'];
		
		// make sure the cache directory is there
		if (! is_dir($this->config['path'])) {
			mkdir($this->config['path']);
		}
	}
	
	
	/**
	* 
	* Fetches cache entry data.
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
		// get the entry filename *before* validating;
		// this avoids race conditions.
		$file = $this->entry($key);
		
		// make sure the file exists and is readable,
		if (file_exists($file) && is_readable($file)) {
			// has the file expired?
			$expire_time = filemtime($file) + $this->config['life'];
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
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @return bool True on success, false on failure.
	* 
	*/
	
	public function replace($key, $data)
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
	* @access public
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
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function deleteAll()
	{
		// open the directory
		$dir = dir($this->config['path']);
		
		// did it exist?
		if ($dir) {
			
			// delete each file in the cache directory.
			// we use the "false !==" piece so that a file
			// named '0' does not prematurely terminate the
			// loop.
			while (false !== ($file = $dir->read())) {
				// delete the file (suppress errors so that . and ..
				// don't throw warnings) ...
				@unlink($this->config['path'] . $file);
				// ... and any serial marker.
				@unlink($file . '.serial');
			}
			
			// done
			$dir->close();
		}
	}
	
	
	/**
	* 
	* Returns the filename for the entry key.
	* 
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @return string The cache entry filename.
	* 
	*/
	
	public function entry($key)
	{
		return $this->config['path'] . md5($key);
	}
} 

?>