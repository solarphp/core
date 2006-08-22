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
 * APC cache controller.
 *
 * The Alternative PHP Cache (APC) is a free and open opcode cache for PHP.
 * It was conceived of to provide a free, open, and robust framework for
 * caching and optimizing PHP intermediate code.
 *
 * The APC extension is not bundled with PHP; you will need to install it
 * on your server before you can use it.
 * More info on the [APC homepage](http://pecl.php.net/package/apc).
 *
 * @category Solar
 *
 * @package Solar_Cache
 *
 */
class Solar_Cache_Adapter_Apc extends Solar_Cache_Adapter {

    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `life`:
     * (int) The cache entry lifetime in seconds, default 0
     * (never expires).
     *
     * @var array
     *
     */
    protected $_Solar_Cache_Adapter_Apc = array(
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
        if (! ( extension_loaded('apc') && ini_get('apc.enabled') ) ) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'apc')
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
        return apc_store($key, $data, $this->_life);
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
        return apc_fetch($key);
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
        apc_delete($key);
    }

    /**
     *
     * Removes all cache entries.
     * 
     * Note that APC makes a distinction between "user" entries and
     * "system" entries; this only deletes the "user" entries.
     *
     * @return void
     *
     */
    public function deleteAll()
    {
        apc_clear_cache('user');
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