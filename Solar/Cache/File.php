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
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'path'   => '/tmp/Solar_Cache_File/',
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
		
		// fix up the path value
		$this->config['path'] = Solar::fixdir($this->config['path']);
		
		// make sure the cache directory is there
		if (! is_dir($this->config['path'])) {
			mkdir($this->config['path']);
		}
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
		// get the entry filename and open for reading
		$file = $this->entry($key);
		$fp = @fopen($file, 'rb');
		
		// could it be opened?
		if ($fp) {
			
			// PHP caches file lengths; clear that out so we get
			// an accurate file length.
			clearstatcache();
			$len = filesize($file);
			
			// shared-lock for reading
			flock($fp, LOCK_SH);
			$data = fread($fp, $len);
			flock($fp, LOCK_UN);
			fclose($fp);
			
			// check for serializing
			if (file_exists($file . '.serial')) {
				$data = unserialize($data);
			}
			
			// done!
			return $data;
		}
		
		// could not open file.
		return false;
	}
	
	
	/**
	* 
	* Sets cache entry data.
	* 
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @return bool True on success, false on failure.
	* 
	*/
	
	public function replace($key, $data, $life = null)
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
			flock($fp, LOCK_UN);
			fclose($fp);
			
			// add a .serial file?
			if ($serial) {
				touch($file . '.serial');
			} else {
				// make sure no serial file is there
				// from a previous entry with the same
				// name
				@unlink($file . '.serial');
			}
			
			// done!
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
				// delete the file ...
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
	* Checks to see if a cache entry is valid.
	* 
	* A cache entry is valid if its file exists, that file is readable,
	* and the file has not passed the cache lifetime in seconds.
	* 
	* Removes the entry if it exists but is no longer valid.
	* 
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public function valid($key, $life)
	{
		// get the entry filename
		$file = $this->entry($key);
		
		// make sure the file exists and is readable,
		if (file_exists($file) && is_readable($file)) {
			
			// has the file expired?
			$expire = filemtime($file) + $life;
			if (time() < $expire) {
				// no, so it's valid!
				return true;
			} else {
				// expired, remove it
				$this->delete($key);
			}
		}
		
		// if we got this far, it's not valid.
		return false;
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