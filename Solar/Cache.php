<?php

/**
* 
* Class for cache control.
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
* Class for cache control.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Cache
* 
*/

class Solar_Cache extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* active  => (bool) setting for $active
	* 
	* class  => (string) the cache driver to use
	* 
	* options => (array) array of config options for the driver
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'active'  => true,
		'class'   => 'Solar_Cache_File',
		'options' => array()
	);
	
	
	/**
	* 
	* Enable/disable caching.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	public $active = true;
	
	
	/**
	* 
	* The instantiated driver object.
	* 
	* @access protected
	* 
	* @var object
	* 
	*/
	
	protected $driver;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An array of configuration options.
	* 
	*/
	
	public function __construct($config = null)
	{
		// basic config option settings
		parent::__construct($config);
		
		// set activity flag
		$this->active = $this->config['active'];
		
		// instantiate a driver object
		$this->driver = Solar::object(
			$this->config['class'],
			$this->config['options']
		);
	}
	
	
	/**
	* 
	* Sets cache entry data.
	* 
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @param string $data The data to store.
	* 
	* @return bool True on success, false on failure.
	* 
	*/
	
	public function set($key, $data)
	{
		if ($this->active) {
			return $this->driver->set($key, $data);
		} else {
			return false;
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
		if ($this->active) {
			return $this->driver->get($key);
		} else {
			return false;
		}
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
		if ($this->active) {
			$this->driver->del($key);
		}
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
		if ($this->active) {
			return $this->driver->valid($key);
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Removes all entities from the cache.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function clear()
	{
		return $this->driver->clear();
	}
	
	
	/**
	* 
	* Returns the driver-specific name for the entry key.
	* 
	* @access public
	* 
	* @param string $key The entry ID.
	* 
	* @return mixed The driver-specific name for the entry key.
	* 
	*/
	
	public function entry($key)
	{
		return $key;
	}
}
?>