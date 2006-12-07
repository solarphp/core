<?php
/**
 *
 * Class for gathering details about the request environment.
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
 * Class for gathering details about the request environment.
 *
 * This is effectively a singleton class; all request variables are 
 * static, and are shared across all Solar_Request instances.
 * 
 * Which keys can be tampered with for XSS insertions?
 * 
 * For SERVER ...
 * 
 * - All HTTP_* keys
 * 
 * - QUERY_STRING
 * 
 * - REMOTE_HOST
 * 
 * - REQUEST_URI
 * 
 * - SERVER_NAME
 * 
 * - PHP_SELF
 * 
 * - PATH_TRANSLATED
 * 
 * - PATH_INFO
 * 
 * - argv
 * 
 * - PHP_AUTH_USER
 * 
 * - PHP_AUTH_PW
 * 
 * - REMOTE_HOST is usually the result of a DNS lookup made by
 *   the webserver, so it's much harder to insert XSS.
 * 
 * For FILES, the 'name' and 'type' keys for each file entry.
 * 
 * @category Solar
 *
 * @package Solar_Request
 * 
 */
class Solar_Request extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `reload`
     * : (bool) Forcibly reload static properties at instantiation time.
     *   Default false, which means properties are loaded only on first
     *   instantiation.
     * 
     * @var array
     * 
     */
    protected $_Solar_Request = array(
        'reload' => false,
    );
    
    /**
     *
     * Reference to static $_request['env'] values.
     *
     * @var array
     *
     */
    public $env;
    
    /**
     *
     * Reference to static $_request['get'] values.
     *
     * @var array
     *
     */
    public $get;
    
    /**
     *
     * Reference to static $_request['post'] values.
     *
     * @var array
     *
     */
    public $post;
    
    /**
     *
     * Reference to static $_request['cookie'] values.
     *
     * @var array
     *
     */
    public $cookie;
    
    /**
     *
     * Reference to static $_request['server'] values.
     *
     * @var array
     *
     */
    public $server;
    
    /**
     *
     * Reference to static $_request['files'] values.
     *
     * @var array
     *
     */
    public $files;
    
    /**
     *
     * Reference to static $_request['http'] values.
     * 
     * These are HTTP headers pulled from from the $_request['server']
     * array.
     * 
     * Header keys are normalized and lower-cased; keys and values are
     * filtered for control characters.
     *
     * @var array
     *
     */
    public $http;
    
    /**
     * 
     * Processed request values.
     * 
     * Note that these are static; they are the same for every instance
     * of Solar_Request.
     * 
     * @var array
     * 
     */
    static protected $_request = array(
        'env'    => array(),
        'get'    => array(),
        'post'   => array(),
        'cookie' => array(),
        'server' => array(),
        'files'  => array(),
        'http'   => array(),
    );
    
    /**
     * 
     * Have values been loaded already?
     * 
     * @var bool
     * 
     */
    static protected $_loaded = false;
    
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
        
        // map public properties to static storage
        $this->env    =& self::$_request['env'];
        $this->get    =& self::$_request['get'];
        $this->post   =& self::$_request['post'];
        $this->cookie =& self::$_request['cookie'];
        $this->server =& self::$_request['server'];
        $this->files  =& self::$_request['files'];
        $this->http   =& self::$_request['http'];
        
        // load values
        $this->load($this->_config['reload']);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$get]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $get key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $get[$key], or the alternate default
     * value.
     * 
     */
    public function get($key = null, $alt = null)
    {
        return $this->_getValue('get', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$post]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $post key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $post[$key], or the alternate default
     * value.
     * 
     */
    public function post($key = null, $alt = null)
    {
        return $this->_getValue('post', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$cookie]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $cookie key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $cookie[$key], or the alternate default
     * value.
     * 
     */
    public function cookie($key = null, $alt = null)
    {
        return $this->_getValue('cookie', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$env]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $env key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $env[$key], or the alternate default
     * value.
     * 
     */
    public function env($key = null, $alt = null)
    {
        return $this->_getValue('env', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$server]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $server key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $server[$key], or the alternate default
     * value.
     * 
     */
    public function server($key = null, $alt = null)
    {
        return $this->_getValue('server', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$files]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $files key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $files[$key], or the alternate default
     * value.
     * 
     */
    public function files($key = null, $alt = null)
    {
        return $this->_getValue('files', $key, $alt);
    }
    
    /**
     * 
     * Retrieves a value by key from the [[$http]] property, or an alternate
     * default value if that key does not exist.
     * 
     * @param string $key The $http key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $http[$key], or the alternate default
     * value.
     * 
     */
    public function http($key = null, $alt = null)
    {
        return $this->_getValue('http', strtolower($key), $alt);
    }
    
    /**
     * 
     * Is this a 'GET' request?
     * 
     * @return bool
     * 
     */
    public function isGet()
    {
        return $this->server('REQUEST_METHOD') == 'GET';
    }
    
    /**
     * 
     * Is this a 'POST' request?
     * 
     * @return bool
     * 
     */
    public function isPost()
    {
        return $this->server('REQUEST_METHOD') == 'POST';
    }
    
    /**
     * 
     * Is this a 'PUT' request?
     * 
     * @return bool
     * 
     */
    public function isPut()
    {
        return $this->server('REQUEST_METHOD') == 'PUT';
    }
    
    /**
     * 
     * Is this a 'DELETE' request?
     * 
     * @return bool
     * 
     */
    public function isDelete()
    {
        return $this->server('REQUEST_METHOD') == 'DELETE';
    }
    
    /**
     * 
     * Is this an 'XML' request?
     * 
     * Checks if the `X-Requested-With` HTTP header is `XMLHttpRequest`.
     * Generally used in addition to the [[isPost()]], [[isGet()]], etc. 
     * methods to identify Ajax-style HTTP requests.
     * 
     * @return bool
     * 
     */
    public function isXml()
    {
        return strtolower($this->http('X-Requested-With')) == 'xmlhttprequest';
    }
    
    /**
     *
     * Loads properties from the superglobal arrays.
     * 
     * Normalizes HTTP header keys, dispels magic quotes.
     * 
     * Subsequent calls will not reload properties, unless the $reload
     * property is set to true.
     * 
     * @param bool $reload If true, reload all properties from the 
     * original superglobal arrays, even if properties have already
     * been loaded.
     * 
     * @return void
     *
     */
    public function load($reload = false)
    {
        if (self::$_loaded && ! $reload) {
            // already loaded and not forcing a reload
            return;
        }
        
        // load the "real" request vars
        $vars = array('env', 'get', 'post', 'cookie', 'server', 'files');
        foreach ($vars as $key) {
            $var = '_' . strtoupper($key);
            if (isset($GLOBALS[$var])) {
                self::$_request[$key] = $GLOBALS[$var];
            } else {
                self::$_request[$key] = array();
            }
        }
        
        // dispel magic quotes if they are enabled.
        // http://talks.php.net/show/php-best-practices/26
        if (get_magic_quotes_gpc()) {
            $in = array(&$_GET, &$_POST, &$_COOKIE);
            while (list($k, $v) = each($in)) {
                foreach ($v as $key => $val) {
                    if (! is_array($val)) {
                        $in[$k][$key] = stripslashes($val);
                        continue;
                    }
                    $in[] =& $in[$k][$key];
                }
            }
            unset($in);
        }
        
        // load the "fake" http request var
        self::$_request['http'] = array();
        foreach (self::$_request['server'] as $key => $val) {
            
            // only retain HTTP headers
            if (substr($key, 0, 4) == 'HTTP') {
                
                // normalize the header key to lower-case
                $nicekey = strtolower(
                    str_replace('_', '-', substr($key, 5))
                );

                // strip control characters from keys and values
                $nicekey = preg_replace('/[\x00-\x1F]/', '', $nicekey);
                self::$_request['http'][$nicekey] = preg_replace('/[\x00-\x1F]/', '', $val);

                // no control characters wanted in self::$_request['server'] for these
                self::$_request['server'][$key] = self::$_request['http'][$nicekey];

                // disallow external setting of X-JSON headers.
                if ($nicekey == 'x-json') {
                    unset(self::$_request['http'][$nicekey]);
                    unset(self::$_request['server'][$key]);
                }
            }
        }
        
        // done!
        self::$_loaded = true;
    }
    
    /**
     * 
     * Common method to get a static request value and return it.
     * 
     * @param string $var The request variable to fetch from: get, post,
     * etc.
     * 
     * @param string $key The array key, if any, to get the value of.
     * 
     * @param string $alt The alternative default value to return if the
     * requested key does not exist.
     * 
     * @return mixed The requested value, or the alternative default
     * value.
     * 
     */
    protected function _getValue($var, $key, $alt)
    {
        // get the whole property, or just one key?
        if ($key === null) {
            // no key selected, return the whole array
            return self::$_request[$var];
        } elseif (array_key_exists($key, self::$_request[$var])) {
            // found the requested key
            return self::$_request[$var][$key];
        } else {
            // requested key does not exist
            return $alt;
        }
    }
}
