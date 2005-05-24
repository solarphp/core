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
* @todo Always do file locking?
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
	* lock => (bool) enable/disable file locking (both read and write)
	* 
	* life => (int) lifetime in seconds for each cache entry
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'path'   => '/tmp/Solar_Cache_File/',
		'lock'   => true,
		'life'   => 3600
	);
	
	
	/**
	* 
	* Path to the cache directory (should be read/write for the web server).
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $path = '/tmp/Solar_Cache_File/';
	
	
	/**
	* 
	* Enable/disable file locking for reads and writes.
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected $lock = true;
	
	
	/**
	* 
	* Lifetime of each cache entry, in seconds.
	* 
	* @access protected
	* 
	* @var int
	* 
	*/
	
	protected $life = 3600;
	
	
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
		$this->path = Solar::fixdir($this->config['path']);
		$this->lock = $this->config['lock'];
		$this->life = $this->config['life'];
		
		// make sure the cache directory is there
		if (! is_dir($this->path)) {
			mkdir($this->path);
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
	
	public function get($key)
	{
		// get the entry filename *before* validating;
		// this avoids a race condition.
		$file = $this->entry($key);
		
		// did we find a valid cache entry?
		if ($this->valid($key)) {
			
			// yes, open it for reading
			$fp = @fopen($file, 'rb');
			
			// could it be opened?
			if ($fp) {
				
				// PHP caches file lengths; clear that out so we get
				// an accurate file length.
				clearstatcache();
				$len = filesize($file);
				
				// lock for reading and read.
				if ($this->lock) {
					flock($fp, LOCK_SH);
				}
				
				$data = fread($fp, $len);
				
				// unlock after reading and close.
				if ($this->lock) {
					flock($fp, LOCK_UN);
				}
				
				fclose($fp);
				
				// check for serializing
				if (file_exists($file . '.serial')) {
					$data = unserialize($data);
				}
				
				// done!
				return $data;
			}
		}
		
		// no valid cache entry, or could not read file.
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
	
	public function set($key, $data)
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
			
			// yes.  lock for writing and write
			if ($this->lock) {
				flock($fp, LOCK_EX);
			}
			fwrite($fp, $data, strlen($data));
			
			// unlock after writing and close.
			if ($this->lock) {
				flock($fp, LOCK_UN);
			}
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
	
	public function del($key)
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
	
	public function clear()
	{
		// open the directory
		$dir = dir($this->path);
		
		// did it exist?
		if ($dir) {
			
			// delete each file in the cache directory.
			// we use the "false !==" piece so that a file
			// named '0' does not prematurely terminate the
			// loop.
			while (false !== ($file = $dir->read())) {
				// delete the file ...
				unlink($this->path . $file);
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
	
	public function valid($key)
	{
		// get the entry filename
		$file = $this->entry($key);
		
		// make sure the file exists and is readable,
		if (file_exists($file) && is_readable($file)) {
			
			// has the file expired?
			if (time() < filemtime($file) + $this->life) {
				// no, so it's valid!
				return true;
			} else {
				// expired, remove it
				$this->del($key);
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
		return $this->path . md5($key);
	}
} 

?>