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
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'host' => 'localhost',
		'port' => '11211',
	);
	
	
	/**
	* 
	* Catalog of when keys were saved into the cache.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $catalog = array();
	
	
	/**
	* 
	* A memcache client object.
	* 
	* @access protected
	* 
	* @var object
	* 
	*/
	
	protected $memcache;
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->memcache = new Memcache;
		$this->memcache->connect($this->config['host'], $this->config['port']);
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
	
	public function replace($key, $data)
	{
		$this->catalog[$key] = time();
		return $this->memcache->set($key, $data);
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
		return $this->memcache->get($key);
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
		unset($this->catalog[$key]);
		$this->memcache->delete($key);
	}
	
	
	/**
	* 
	* Checks if a cache entry exists and is not past its lifetime.
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
		// is the entry in the catalog?
		if (empty($this->catalog[$key])) {
			echo "not in catalog\n";
			return false;
		}
		
		// is the entry past its lifetime?
		if ($this->catalog[$key] + $life > time()) {
			// past its lifetime, remove from the cache
			echo "past lifetime\n";
			unset($this->catalog[$key]);
			$this->delete($key);
			return false;
		}
		
		// assume it's still at the cache
		return true;
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
		$this->memcache->flush();
	}
}
?>