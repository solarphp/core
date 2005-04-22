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
	
	public $config = array(
		'host' => 'localhost',
		'port' => '11211'
		'life' => 60
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
	
	public function set($key, $data)
	{
		return $this->memcache->set($key, $data, null, $this->config['life']);
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
	
	public function del($key)
	{
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
	
	public function valid($key)
	{
		if ($this->memcache->get($key)) {
			return true;
		} else {
			return false;
		}
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
	
	public function clear()
	{
		$this->memcache->flush();
	}
}
?>