<?php
/**
 *
 * Memcache cache controller.
 *
 * @category Solar
 *
 * @package Solar_Cache
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * The abstract cache adapter.
 */
Solar::loadClass('Solar_Cache_Adapter');

/**
 *
 * XCache cache controller.
 *
 * XCache is a fast, stable PHP opcode cacher tested and supported on
 * all of the latest PHP cvs branches.
 *
 * The XCache extension is not bundled with PHP; you will need to
 * install it on your server before you can use it. More info on the
 * [XCache homepage](http://trac.lighttpd.net/xcache/wiki/).
 *
 * @category Solar
 *
 * @package Solar_Cache
 *
 */
class Solar_Cache_Adapter_Xcache extends Solar_Cache_Adapter {

    /**
     *
     * User-provided configuration.
     *
     * Keys are:
     *
     * : \\life\\ : (int) The cache entry lifetime in seconds, default 0
     * (never expires).
     *
     * @var array
     *
     */
    protected $_Solar_Cache_Adapter_Xcache = array(
        'life' => 0,
    );

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // make sure we have apc available
        if (! (extension_loaded('xcache') && ini_get('xcache.cacher'))) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'xcache')
            );
        }

        // we're ok
        parent::__construct($config);
    }

    /**
     *
     * Sets cache entry data.
     *
     * @param string $key The entry ID.
     *
     * @param mixed $data The data to write into the entry.
     *
     * @return bool True on success, false on failure.
     *
     */
    public function save($key, $data)
    {
        return xcache_set($key, $data, $this->_life);
    }

    /**
     *
     * Gets cache entry data.
     *
     * @param string $key The entry ID.
     *
     * @return mixed Boolean false on failure, cache data on success.
     *
     */
    public function fetch($key)
    {
        return xcache_get($key);
    }

    /**
     *
     * Deletes a cache entry.
     *
     * @param string $key The entry ID.
     *
     * @return void
     *
     */
    public function delete($key)
    {
        xcache_unset($key);
    }

    /**
     *
     * Removes all cache entries.
     *
     * @param mixed 'user' to delete user variables & cached scripts,
     * null to delete only cached scripts
     *
     * @return void
     *
     */
    public function deleteAll($cache_type = 'user')
    {
        // XC_TYPE_VAR to clear user variables or
        // XC_TYPE_PHP to delete cached scripts
        if($cache_type == 'user') {
            // clear user cache
            xcache_clear_cache('XC_TYPE_VAR');
        }
        xcache_clear_cache('XC_TYPE_PHP');
    }

    /**
     *
     * Returns the name for the entry key.
     *
     * @param string $key The entry ID.
     *
     * @return string The cache entry name.
     *
     */
    public function entry($key)
    {
        return $key;
    }
}
?>