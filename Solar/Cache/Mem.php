<?php

/**
* 
* Memcached cache controller.
* 
* @category Solar
* 
* @package Solar_Cache
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Mem.php,v 1.7 2005/02/08 01:42:26 pmjones Exp $
* 
*/

/**
* 
* Memcached cache controller.
* 
* @category Solar
* 
* @package Solar_Cache
* 
*/

class Solar_Cache_Mem extends Solar_Base {
	
	
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
	* life => (int) lifetime in seconds for each cached entity
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
		return $this->memcache->set($key, $data, null, $this->config['life']);
	}
	
	
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
		return $this->memcache->get($key);
	}
	
	
	/**
	* 
	* Deletes a cached entity.
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
		$this->memcache->delete($key);
	}
	
	
	/**
	* 
	* Checks if a cached entity exists and is not past its lifetime.
	* 
	* @access public
	* 
	* @param string $key The entity ID.
	* 
	* @param bool $remove Enable/disable removal of invalid entities.
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
	* Removes all cache entities.
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