<?php
/**
 * 
 * Class for cache control.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
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
 * [[toc]]
 * 
 * ++ Overview
 * 
 * Solar_Cache is a cousin to [http://pear.php.net/Cache_Lite PEAR
 * Cache_Lite], although most of the options have been made standard
 * behavior.
 * 
 * Solar_Cache is itself a facade class; its methods interface
 * directly with a backend container or driver class that handles the
 * actual storage or retrieval.  Solar_Cache comes with two
 * CacheDrivers (or container classes): the FileDriver and the
 * MemcacheDriver.  Although the configuration options for the
 * drivers are slightly different, the API for them is identical
 * (becuase the API is mapped via the master Solar_Cache class).
 * 
 * ++ Examples
 * 
 * +++ Instantiation
 * 
 * To instantiate Solar_Cache, you need to pick which driver you
 * want.  In the following example, we'll set up a FileDriver cache
 * (which stores data as a file on the hard drive).
 * 
 * <code type="php">
 * require_once 'Solar.php';
 * Solar::start();
 * 
 * // set up the cache options
 * $config = array(
 *     'class' => 'Solar_Cache_File',     // which driver class to use
 *     'options' => array(                // the config options for the class
 *         'path' => '/tmp/Solar_Cache/', // where the cache files will be stored
 *         'life' => 1800,                // the cache entry lifetime in seconds
 *     ),
 * );
 * 
 * // create a cache object
 * $cache = Solar::factory('Solar_Cache', $config);
 * </code>
 * 
 * +++ Cache Drivers
 * 
 * Solar_Cache comes with two drivers, or containers, for cached
 * data:
 * 
 * * the [Solar_Cache_File:HomePage Solar_Cache_File] driver, which
 * stores data in the filesystem
 * 
 * * the [Solar_Cache_Memcache:HomePage Solar_Cache_Memcache], which
 * uses [[php memcache]] for storing data in memory
 * 
 * You can write your own cache driver very easily.  Use the
 * Solar_Cache_File or Solar_Cache_Memcache classes as a template,
 * and implement the various methods therein with the same parameter
 * lists.  Then you can use that custom class name as the Solar_Cache
 * 'class' config key value.
 * 
 * +++ Driver Configuration
 * 
 * Note that the config keys for the Solar_Cache class are logically
 * separate from the config keys for its driver classes.  This means
 * you can configure the Solar_Cache, Solar_Cache_File, and
 * Solar_Cache_Memcache classes separately within the
 * [Main:ConfigFile config file] and Solar will use those options
 * automatically.
 * 
 * For example, you can do this in your config file...
 * 
 * <code type="php">
 * $config = array();
 * 
 * // ...
 * 
 * $config['Solar_Cache_File'] => array(
 *     'path' => '/tmp/Solar_Cache',
 *     'life' => 1800,
 * );
 * 
 * $config['Solar_Cache'] => array(
 *     'class' => 'Solar_Cache_File'
 * );
 * 
 * // ...
 * 
 * return $config;
 * </code>
 * 
 * ... and then instantiate a cache exactly like the above example
 * (because the config file already has the options defined for you)
 * with just one line:
 * 
 * <code type="php">
 * require_once 'Solar.php';
 * Solar::start();
 * 
 * // create a cache object
 * $cache = Solar::factory('Solar_Cache');
 * </code>
 * 
 * 
 * +++ Usage
 * 
 * Once you have instantiated the cache, you can use it to store data
 * that usually takes more resources to generate than a filesystem
 * call does (in the case of the FileDriver) or a memory call (in the
 * case of the MemcacheDriver).
 * 
 * In the following example, we simulate caching the results of a
 * database call or other resource-intensive task.  If the cache
 * entry does not exist, we generate the data and save it in the
 * cache; if it does exist, we use that instead (thus speeding up the
 * script execution).
 * 
 * <code type="php">
 * require_once 'Solar.php';
 * Solar::start();
 * 
 * // connect to the cache
 * $cache = Solar::factory('Solar_Cache');
 * 
 * // every cache entry needs a unique ID; we'll assume that ID
 * // is passed as part of the URL.
 * $id = Solar::get('id');
 * 
 * // try to get the cache entry.  if the entry is past its lifetime,
 * // this will addiitonally delete the entry for us, keeping the cache
 * // clean.
 * $output = $cache->fetch($id);
 * 
 * // did we get it?
 * if (! $output) {
 *     // no output stored in the cache under that ID.
 *     // regenerate it ...
 * 
 *     // (assume we connect to a database and transform the data in 
 *     // some way, and call it $output).
 * 
 *     // ... and save the output in the cache, replacing anything that
 *     // may have been in that entry before.
 *     $cache->save($id, $output);
 * }
 * 
 * // now we have the output, whether from the cache
 * // or from generating it fresh., we can output it or do whatever else we need.
 * echo $output;
 * </code>
 * 
 * ++ Limitations and Considerations
 * 
 * Because Solar_Cache is intended work exactly the same with every
 * underlying driver for it, there are some special considerations to
 * take into account.
 * 
 * For example, the PEAR Cache_Lite class allows you to specify
 * "groups" of caches through a single cache object; because the
 * MemcacheDriver has no way of implementing that kind of function,
 * it is not implemented within the FileDriver either.  However, the
 * easy workaround it to have a separate Solar_Cache object for each
 * "group" of caches you want to set up.
 * 
 * If you are on a shared system, you need to make sure the
 * FileDriver path is not shared between separate users or
 * installations, otherwise the installations will "compete" with
 * each other when entry keys are identical.  The easy way to work
 * around this is to not to use a system temp directory, and instead
 * set up a directory specifically for caching.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Cache extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'active'  => true,
        'class'   => 'Solar_Cache_File',
        'options' => null
    );
    
    /**
     * 
     * The instantiated driver object.
     * 
     * @var object
     * 
     */
    protected $_driver;
    
    /**
     * 
     * Constructor.
     * 
     * Config keys are:
     * 
     * : \\class\\   : (string) The name of the cache driver class, default 'Solar_Cache_File'
     * : \\options\\ : (array) An array of config key options to use when instantiating the driver class
     * : \\active\\  : (bool) Whether or not the cache is active at instantiation time (default true)
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // instantiate a driver object
        $this->_driver = Solar::factory(
            $this->_config['class'],
            $this->_config['options']
        );
    }
    
    /**
     * 
     * Makes the cache active (true) or inactive (false).
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // turn the cache off
     * $cache->setActive(false);
     * 
     * // turn it back on
     * $cache->setActive(true);
     * </code>
     * 
     * @param bool $flag True to turn on, false to turn off.
     * 
     */
    public function setActive($flag)
    {
        $this->_config['active'] = (bool) $flag;
    }
    
    /**
     * 
     * Gets the current activity state of the cache (on or off).
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // is the cache active or not?
     * $active = $cache->active();
     * Solar::dump($active);
     * </code>
     * 
     * @return bool True if active, false if not.
     * 
     */
    public function active()
    {
        return $this->_config['active'];
    }
    
    /**
     * 
     * Returns the cache lifetime in seconds.
     * 
     * @return int The cache lifetime in seconds.
     * 
     */
    public function life()
    {
        return $this->_driver->life();
    }
    
    
    /**
     * 
     * Inserts/updates cache entry data.
     * 
     * This method stores data to the cache with its own entry
     * identifier.  If the entry does not exist, it is created; if
     * the entry does already exist, it is updated with the new data.
     * 
     * Does not replace if caching is not active.
     * 
     * For example, to store an array in the cache ...
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create a unique ID
     * $id = 'my_array';
     * 
     * // set up some data to cache (this could be string output, or
     * // an object, or almost anything else)
     * $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
     * 
     * // store to the cache, overwriting any previous $id entry
     * $cache->save($id, $data);
     * 
     * // now fetch back the data for the $id entry
     * $result = $cache->fetch($id);
     * 
     * // $data and $result should be identical
     * </code>

     * @param string $key The entry ID.
     * 
     * @param string $data The data to store.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function save($key, $data)
    {
        if ($this->active()) {
            return $this->_driver->save($key, $data);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * Use this to retrieve the cache entry identifed by key.  The
     * key can be any scalar value:  a web page name, an integer ID,
     * a simple name, and so on.
     * 
     * If the cache entry does not exist, or if it has passed its
     * lifetime (defined in the driver's config keys), the
     * function will return boolean false; otherwise, it will return
     * the contents of the cache entry.
     * 
     * For example, to get a cache entry identified by a web page
     * name, you could do this:
     * 
     * <code type="php">
     * // create a cache object
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // get the request URI as an identifier
     * $id = Solar::server('REQUEST_URI');
     * 
     * // fetch the result and dump it to screen
     * $result = $cache->fetch($id);
     * Solar::dump($result);
     * </code>

     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    public function fetch($key)
    {
        if ($this->active()) {
            return $this->_driver->fetch($key);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create an entry ID named for the current URI
     * $id = Solar::server('REQUEST_URI');
     * 
     * // delete any cache entry with that ID
     * $cache->delete($id);
     * </code>

     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        if ($this->active()) {
            return $this->_driver->delete($key);
        }
    }
    
    /**
     * 
     * Deletes all entries from the cache.
     * 
     * Example:
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // delete all entries
     * $cache->deleteAll();
     * </code>

     * @return void
     * 
     */
    public function deleteAll()
    {
        if ($this->active()) {
            return $this->_driver->deleteAll();
        }
    }
    
    /**
     * 
     * Returns the driver-specific name for the entry key.
     * 
     * Cache drivers do not always use the identifier you specify for
     * cache entries.  For example, the [Solar_Cache_File:HomePage file driver]
     * names the cache entries based on an MD5 hash of the entry ID. 
     * This method tells you what the driver is using as the name for
     * the cache entry.
     * 
     * <code type="php">
     * $cache = Solar::factory('Solar_Cache');
     * 
     * // create an entry ID named for the current URI
     * $id = Solar::server('REQUEST_URI');
     * 
     * // find out what the underlying cache driver uses as the entry name
     * $real_name = $cache->entry($id);
     * </code>

     * @param string $key The entry ID.
     * 
     * @return mixed The driver-specific name for the entry key.
     * 
     */
    public function entry($key)
    {
        return $this->_driver->entry($key);
    }
}
?>