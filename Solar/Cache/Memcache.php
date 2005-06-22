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
	
	public function replace($key, $data, $life)
	{
		return $this->memcache->replace($key, $data, $life);
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
		return (bool) $this->memcache->get($key);
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