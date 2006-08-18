<?php
/**
 *
 * Class for gathering details about the request environment and tweaking it
 * as necessary.
 *
 * @category Solar
 *
 * @package Solar_Request
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Class for gathering details about the request environment and tweaking it
 * as necessary.
 *
 * @category Solar
 *
 * @package Solar_Request
 *
 */
class Solar_Request extends Solar_Base {

    /**
     *
     * Copy of the original $_GET superglobal
     *
     * @var array
     *
     */
    protected $_originalGet;

    /**
     *
     * Copy of the original $_POST superglobal
     *
     * @var array
     *
     */
    protected $_originalPost;

    /**
     *
     * Copy of the original $_SERVER superglobal
     *
     * @var array
     *
     */
    protected $_originalServer;

    /**
     *
     * Copy of the original $_ENV superglobal
     *
     * @var array
     *
     */
    protected $_originalEnv;

    /**
     *
     * Copy of the original $_FILES superglobal
     *
     * @var array
     *
     */
    protected $_originalFiles;

    /**
     *
     * Copy of the original $_COOKIE superglobal
     *
     * @var array
     *
     */
    protected $_originalCookie;

    /**
     *
     * Processed $_GET
     *
     * @var array
     *
     */
    protected $_get;

    /**
     *
     * Processed $_POST
     *
     * @var array
     *
     */
    protected $_post;

    /**
     *
     * Processed $_SERVER
     *
     * @var array
     *
     */
    protected $_server;

    /**
     *
     * Processed $_ENV
     *
     * @var array
     *
     */
    protected $_env;

    /**
     *
     * Processed $_FILES
     *
     * @var array
     *
     */
    protected $_files;

    /**
     *
     * Processed $_COOKIE
     *
     * @var array
     *
     */
    protected $_cookie;

    /**
     *
     * Processed HTTP Headers
     *
     * @var array
     *
     */
    protected $_headers;

    /**
     *
     * Constructor.
     *
     * @param array $config User-defined configuration values.
     *
     */
    public function __construct($config = null)
    {

        parent::__construct($config);

        // make copies of the original superglobal values
        $this->_setOriginals();

        // clear registered globals
        $this->_clearRegisteredGlobals();

        // Undo magic of magic quotes
        $this->_undoQuotes();

        // Sets SAPI-agnostic getallheaders() equiv
        $this->_setCleanHeaders();

        // Additional cleaning/filtering methods here


        // Processing on superglobals complete, copy superglobals
        // into "usable" obj vars
        $this->_setCleanGlobals();
    }

    /**
     *
     * get/post/server/env/files/cookie/headers getters, which return massaged
     * values.
     *
     * @param string $name Name of getter
     *
     * @param string $key Optional key of desired array to retrieve
     *
     * @return mixed Full array or array value
     *
     */
    public function __call($name, $args = null)
    {
        $_name = '_'.$name;

        // Determine if a specific key was requested, and set what
        // default value should be returned if key is not found
        if (is_array($args)) {
            $key = $args[0];
            $default = null;
            if (isset($args[1])) {
                $default = $args[1];
            }
        }

        if (is_array($this->{$_name})) {
            if ($key === null) {
                return $this->{$_name};
            }
            if (isset($this->{$_name}[$key])) {
                return $this->{$_name}[$key];
            }
            return $default;
        } else {
            // No idea what was being asked for
            return null;
        }

        // No idea what was being asked for
        return null;
    }

    /**
     *
     * Makes copies of the original superglobal arrays
     *
     * @return void
     *
     */
    protected function _setOriginals()
    {
        if (isset($_GET)) {
            $this->_originalGet = $_GET;
        }

        if (isset($_POST)) {
            $this->_originalPost = $_POST;
        }

        if (isset($_SERVER)) {
            $this->_originalServer = $_SERVER;
        }

        if (isset($_ENV)) {
            $this->_originalEnv = $_ENV;
        }

        if (isset($_FILES)) {
            $this->_originalFiles = $_FILES;
        }

        if (isset($_COOKIE)) {
            $this->_originalCookie = $_COOKIE;
        }
    }

    /**
     *
     * Clears out registered globals if register_globals is on
     *
     * @return void
     *
     */
    protected function _clearRegisteredGlobals()
    {

        // clear out registered globals?
        // (this code from Richard Heyes and Stefan Esser)
        if (ini_get('register_globals')) {

            // Variables that shouldn't be unset
            $noUnset = array(
                'GLOBALS', '_GET', '_POST', '_COOKIE',
                '_REQUEST', '_SERVER', '_ENV', '_FILES'
            );

            // sources of global input.
            //
            // the ternary check on $_SESSION is to make sure that
            // it's really an array, not just a string; if it's just a
            // string, that can bypass this check somehow.  Stefan
            // Esser knows how this works, but I don't.
            $input = array_merge($_GET, $_POST, $_COOKIE,
                $_SERVER, $_ENV, $_FILES,
                isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array()
            );

            // unset globals set from input sources, but don't unset
            // the sources themselves.
            foreach ($input as $k => $v) {
                if (! in_array($k, $noUnset) && isset($GLOBALS[$k])) {
                    unset($GLOBALS[$k]);
                }
            }

            // Don't need $input anymore
            unset($input);
        }
    }

    /**
     *
     * Undo the magic of magic_quotes
     *
     * @return void
     *
     */
    protected function _undoQuotes()
    {

        // remove magic quotes if they are enabled; sybase quotes
        // override normal quotes.

        // from ilia's security talk
        if (get_magic_quotes_gpc()) {

            $in = array(&$_GET, &$_POST, &$_COOKIE);

            while (list($k,$v) = each($in)) {

                foreach ($v as $key => $val) {

                    if (!is_array($val)) {

                        if (ini_get('magic_quotes_sybase')) {
                            // sybase quotes
                            $in[$k][$key] = str_replace("''", "'", $val);
                        } else {
                            $in[$k][$key] = stripslashes($val);
                        }
                        continue;
                    }

                    $in[] =& $in[$k][$key];
                }
            }
            unset($in);
        }

        // make sure automatic quoting of values from, e.g., SQL sources
        // is turned off. turn off sybase quotes too.
        ini_set('magic_quotes_runtime', false);
        ini_set('magic_quotes_sybase',  false);

    }

    /**
     *
     * Gets HTTP headers out of $_SERVER (since getallheaders() is Apache-only),
     * corrects key case and formatting, and strips illegal characters from header
     * values.
     *
     * Cleaned results are put into the $_headers array.
     *
     * @return void
     *
     */
    protected function _setCleanHeaders()
    {
        $http = array();

        foreach ($_SERVER as $key => $val) {
            if (substr($key, 0, 4) == 'HTTP') {
                $nicekey = str_replace(' ', '-',
                                ucwords(
                                    strtolower(
                                        str_replace('_', ' ', substr($key, 5)))));

                // Strip control characters from keys and values
                $nicekey = preg_replace('/[\\x00-\\x1F]/', '', $nicekey);

                $http[$nicekey] = preg_replace('/[\\x00-\\x1F]/', '', $val);

                // No control characters wanted in $_SERVER for these
                $_SERVER[$key] = $http[$nicekey];

                // WE do the setting of X-JSON headers, bucko.
                if ($nicekey == 'X-Json') {
                    unset($http[$nicekey]);
                    unset($_SERVER[$key]);
                }

            }
        }

        // Done! Store result
        $this->_headers = $http;
    }

    /**
     *
     * Copies the tweaked versions of superglobals into object properties.
     *
     * @return void
     *
     */
    protected function _setCleanGlobals()
    {
        if (isset($_GET)) {
            $this->_get = $_GET;
        }

        if (isset($_POST)) {
            $this->_post = $_POST;
        }

        if (isset($_SERVER)) {
            $this->_server = $_SERVER;
        }

        if (isset($_ENV)) {
            $this->_env = $_ENV;
        }

        if (isset($_FILES)) {
            $this->_files = $_FILES;
        }

        if (isset($_COOKIE)) {
            $this->_cookie = $_COOKIE;
        }
    }

}
?>