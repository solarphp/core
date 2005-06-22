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
	
	protected $config = array(
		'active'  => true,
		'life'    => 3600,
		'class'   => 'Solar_Cache_Driver_File',
		'options' => array()
	);
	
	
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
		$this->setActive($this->config['active']);
		
		// set cache lifetime
		$this->setLife($this->config['life']);
		
		// instantiate a driver object
		$this->driver = Solar::object(
			$this->config['class'],
			$this->config['options']
		);
	}
	
	
	/**
	* 
	* Turns caching on and off.
	* 
	* @access public
	* 
	* @param bool $flag True to turn on, false to turn off.
	* 
	*/
	
	public function setActive($flag)
	{
		$this->config['active'] = (bool) $flag;
	}
	
	
	/**
	* 
	* Returns the current caching activity flag.
	* 
	* @access public
	* 
	* @return bool True if active, false if not.
	* 
	*/
	
	public function active()
	{
		return (bool) $this->config['active'];
	}
	
	
	/**
	* 
	* Sets the lifetime of the cache in seconds.
	* 
	* @access public
	* 
	* @param int $seconds The lifetime of the cache in seconds.
	* 
	*/
	
	public function setLife($seconds)
	{
		$this->config['life'] = (int) $seconds;
	}
	
	
	/**
	* 
	* Returns the cache lifetime.
	* 
	* @access public
	* 
	* @return int The lifetime of the cache in seconds.
	* 
	*/
	
	public function life()
	{
		return $this->config['life'];
	}
	
	
	/**
	* 
	* Inserts/updates cache entry data.
	* 
	* Does not replace if caching is not active.
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
	
	public function replace($key, $data)
	{
		if ($this->active()) {
			return $this->driver->replace($key, $data, $this->life());
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Fetches cache entry data.
	* 
	* No data is returned if caching is not active.
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
		if ($this->active() && $this->valid($key)) {
			return $this->driver->fetch($key);
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
	
	public function delete($key)
	{
		if ($this->active()) {
			$this->driver->delete($key);
		}
	}
	
	
	/**
	* 
	* Checks if a cache entry exists and is not past its lifetime.
	* 
	* No entry can be valid if caching is not active.
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
		if ($this->active()) {
			return $this->driver->valid($key, $this->life());
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Deletes all entities from the cache.
	* 
	* Does nothing if caching is not active.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function deleteAll()
	{
		if ($this->active()) {
			$this->driver->deleteAll();
		}
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
		return $this->driver->entry($key);
	}
}
?>