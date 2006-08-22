<?php
/**
 *
 * Xcache cache controller.
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
     * Keys are ...
     *
     * `life`:
     * _(int)_ The cache entry lifetime in seconds, default `0`
     * (never expires).
     *
     * `user`:
     * _(string)_ Admin user name for Xcache, as set in php.ini. This login
     * and the corresponding password are required _only_ for the deleteAll()
     * method. Defaults to `null`.
     *
     * `pass`:
     * _(string)_ Plaintext password that matches the md5() encrypted password
     * in php.ini. This password and the corresponding login are required
     * _only_ for the deleteAll() method. Defaults to `null`.
     *
     * @var array
     *
     */
    protected $_Solar_Cache_Adapter_Xcache = array(
        'life' => 0,
        'user' => null,
        'pass' => null
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
     * @return mixed NULL on failure, cache data on success.
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
     * @return bool true on successful deletion, false on failure
     *
     */
    public function delete($key)
    {
        return xcache_unset($key);
    }

    /**
     *
     * Removes all cache entries.
     * 
     * Note that Xcache makes a distinction between "user" entries and
     * "system" or "script" entries; this deletes only "user entries".
     * 
     * @return bool true on success, false on failure
     *
     */
    public function deleteAll()
    {
        // store creds current state
        $olduser = null;
        $oldpass = null;
        
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $olduser = $_SERVER['PHP_AUTH_USER'];
        }
        
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $oldpass = $_SERVER['PHP_AUTH_PW'];
        }
        
        // force credentials to the configured values
        $_SERVER['PHP_AUTH_USER'] = $this->_config['user'];
        $_SERVER['PHP_AUTH_PW'] = $this->_config['pass'];

        // clear user cache
        $vcnt = xcache_count(XC_TYPE_VAR);
        for ($i = 0; $i < $vcnt; $i++) {
            if (!xcache_clear_cache(XC_TYPE_VAR, $i)) {
                return false;
            }
        }

        // Restore creds to prior state
        if ($olduser !== null) {
            $_SERVER['PHP_AUTH_USER'] = $olduser;
        } else {
            $_SERVER['PHP_AUTH_USER'] = null;
        }
        
        if ($oldpass !== null) {
            $_SERVER['PHP_AUTH_PW'] = $oldpass;
        } else {
            $_SERVER['PHP_AUTH_PW'] = null;
        }

        return true;
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