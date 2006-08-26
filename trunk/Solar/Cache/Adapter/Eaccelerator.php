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
 * eAccelerator is a free open-source PHP accelerator, optimizer,
 * encoder and dynamic content cache. It increases the performance of
 * PHP scripts by caching them in their compiled state, so that the
 * overhead of compiling is almost completely eliminated.
 *
 * eAccelerator is not bundled with PHP; you will need to install it
 * on your server before you can use it.  More info on the
 * [eAccelerator homepage](http://eaccelerator.net/).
 *
 * @category Solar
 *
 * @package Solar_Cache
 *
 */
class Solar_Cache_Adapter_Eaccelerator extends Solar_Cache_Adapter {

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
    protected $_Solar_Cache_Adapter_Eaccelerator = array(
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
        if (! (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'eaccelerator')
            );
        }

        // we're ok
        parent::__construct($config);
    }

    /**
     *
     * Sets cache entry data. eAccelerator doesn't serialize object, so
     * you need to do it yourself or php will segfault on object retrieval.
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
        return eaccelerator_put($key, $data, $this->_life);
    }

    /**
     *
     * Gets cache entry data.
     *
     * @param string $key The entry ID.
     *
     * @return mixed Boolean false on failure, string on success.
     *
     */
    public function fetch($key)
    {
        return eaccelerator_get($key);
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
        eaccelerator_rm($key);
    }

    /**
     *
     * Removes all cache entries.
     *
     * @return void
     *
     */
    public function deleteAll()
    {
        eaccelerator_clean();
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