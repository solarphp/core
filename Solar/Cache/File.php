<?php

/**
* 
* File-based cache controller.
* 
* @category Solar
* 
* @package Solar
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
	* hash => (bool) enable/disable name hashing for obscurity
	* 
	* lock => (bool) enable/disable file locking (both read and write)
	* 
	* prefix => (string) prefix for cache entity file names
	* 
	* serial => (string) suffix for marking serialized cache files
	* 
	* life => (int) lifetime in seconds for each cached entity
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'path' => '/tmp/',
		'hash' => true,
		'lock' => true,
		'prefix' => 'Solar_Cache_File_',
		'serial' => '.serial',
		'life' => 3600
	);
	
	
	/**
	* 
	* Gets cached entity data.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
	* 
	* @return mixed Boolean false on failure, string on success.
	* 
	*/
	
	public function get($key)
	{
		// get the entity filename *before* validating;
		// this avoids a race condition.
		$file = $this->filename($key);
		
		// did we find a valid cache entity?
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
				if ($this->config['lock']) {
					flock($fp, LOCK_EX);
				}
				$data = fread($fp, $len);
				
				// unlock after reading and close.
				if ($this->config['lock']) {
					flock($fp, LOCK_UN);
				}
				
				fclose($fp);
				
				// check for serializing
				if (file_exists($file . $this->config['serial'])) {
					$data = unserialize($data);
				}
				
				// done!
				return $data;
			}
		}
		
		// no valid cache entity, or could not read file.
		return false;
	}
	
	
	/**
	* 
	* Sets cached entity data.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
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
		$file = $this->filename($key);
		$fp = @fopen($file, 'wb');
		
		// was it opened?
		if ($fp) {
			
			// yes.  lock for writing and write
			if ($this->config['lock']) {
				flock($fp, LOCK_EX);
			}
			fwrite($fp, $data, strlen($data));
			
			// unlock after writing and close.
			if ($this->config['lock']) {
				flock($fp, LOCK_UN);
			}
			fclose($fp);
			
			// add a .serial file?
			if ($serial) {
				touch($file . $this->config['serial']);
			}
			
			// done!
			return true;
		}
	
		// could not open the file for writing.
		return false;
	}
	
	
	/**
	* 
	* Deletes an entity from the cache.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
	* 
	* @return void
	* 
	*/
	
	public function del($key)
	{
		$file = $this->filename($key);
		unlink($file);
		@unlink($file . $this->config['serial']);
	}
	
	
	/**
	* 
	* Removes all entities from one cache group.
	* 
	* @access public
	* 
	* @param string $group The entity group.
	* 
	* @return void
	* 
	*/
	
	public function clear()
	{
		// open the directory
		$dir = dir($this->config['path']);
		if ($dir) {
		
			// loop through the files in the directory
			// and delete one-by-one, making sure they
			// match the base filename.
			$len = strlen($this->config['prefix']);
			
			// we use the "false !==" piece so that a file
			// named '0' does not prematurely terminate the
			// loop.
			while (false !== ($file = $dir->read())) {
				// only delete files that match the caching filename
				// convention
				if (substr($file, 0, $len) == $this->config['prefix']) {
					// delete the file ...
					unlink($this->config['path'] . $file);
					// ... and any serial marker.
					@unlink($file . $this->config['serial']);
				}
			}
			
			// done
			$dir->close();
		}
	}
	
	
	/**
	* 
	* Checks to see if a cached entity is valid.
	* 
	* A cached entity is valid if its file exists, that file is readable,
	* and the file has not passed the cache lifetime in seconds.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
	* 
	* @param bool $remove If a file has expired, remove it.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public function valid($key)
	{
		// get the entity filename
		$file = $this->filename($key);
		
		// make sure the file exists and is readable,
		if (file_exists($file) && is_readable($file)) {
			
			// has the file expired?
			if (time() < filemtime($file) + $this->config['life']) {
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
	* Determines the filename for a cached entity.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public function filename($key)
	{
		// path and filename prefix
		$file = $this->config['path'] . $this->config['prefix'];
		
		// are we hashing to obfuscate IDs?
		if ($this->config['hash']) {
			// yes, obfuscate
			$file .= md5($key);
		} else {
			// no, use plaintext
			$file .= $key;
		}
		
		// done
		return $file;
	}
} 

?>
