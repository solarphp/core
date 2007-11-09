<?php
/**
 * 
 * Class for gathering details about the request environment.
 * 
 * To be safe, treat everything in the superglobals as tainted.
 * 
 * @category Solar
 * 
 * @package Solar
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
class Solar_Request extends Solar_Base {
    
    /**
     * 
     * Imported $_ENV values.
     * 
     * @var array
     * 
     */
    public $env;
    
    /**
     * 
     * Imported $_GET values.
     * 
     * @var array
     * 
     */
    public $get;
    
    /**
     * 
     * Imported $_POST values.
     * 
     * @var array
     * 
     */
    public $post;
    
    /**
     * 
     * Imported $_COOKIE values.
     * 
     * @var array
     * 
     */
    public $cookie;
    
    /**
     * 
     * Imported $_SERVER values.
     * 
     * @var array
     * 
     */
    public $server;
    
    /**
     * 
     * Imported $_FILES values.
     * 
     * @var array
     * 
     */
    public $files;
    
    /**
     * 
     * Imported $_SERVER['HTTP_*'] values.
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
     * Imported $_SERVER['argv'] values.
     * 
     * @var array
     * 
     */
    public $argv;
    
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
        $this->reset();
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[$get]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$post]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$cookie]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$env]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$server]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$files]] property,
     * or an alternate default value if that key does not exist.
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
     * Retrieves an **unfiltered** value by key from the [[$argv]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $argv key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $argv[$key], or the alternate default
     * value.
     * 
     */
    public function argv($key = null, $alt = null)
    {
        return $this->_getValue('argv', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[$http]] property,
     * or an alternate default value if that key does not exist.
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
     * Is this a command-line request?
     * 
     * @return bool
     * 
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
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
     * Is this an XmlHttpRequest?
     * 
     * Checks if the `X-Requested-With` HTTP header is `XMLHttpRequest`.
     * Generally used in addition to the [[isPost()]], [[isGet()]], etc. 
     * methods to identify Ajax-style HTTP requests.
     * 
     * @return bool
     * 
     */
    public function isXhr()
    {
        return strtolower($this->http('X-Requested-With')) == 'xmlhttprequest';
    }
    
    /**
     * 
     * Reloads properties from the superglobal arrays.
     * 
     * Normalizes HTTP header keys, dispels magic quotes.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        // load the "real" request vars
        $vars = array('env', 'get', 'post', 'cookie', 'server', 'files');
        foreach ($vars as $key) {
            $var = '_' . strtoupper($key);
            if (isset($GLOBALS[$var])) {
                $this->$key = $GLOBALS[$var];
            } else {
                $this->$key = array();
            }
        }
        
        // dispel magic quotes if they are enabled.
        // http://talks.php.net/show/php-best-practices/26
        if (get_magic_quotes_gpc()) {
            $in = array(&$this->get, &$this->post, &$this->cookie);
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
        
        // load the "fake" argv request var
        $this->argv = (array) $this->server('argv');
        
        // load the "fake" http request var
        $this->http = array();
        foreach ($this->server as $key => $val) {
            
            // only retain HTTP headers
            if (substr($key, 0, 4) == 'HTTP') {
                
                // normalize the header key to lower-case
                $nicekey = strtolower(
                    str_replace('_', '-', substr($key, 5))
                );
                
                // strip control characters from keys and values
                $nicekey = preg_replace('/[\x00-\x1F]/', '', $nicekey);
                $this->http[$nicekey] = preg_replace('/[\x00-\x1F]/', '', $val);
                
                // no control characters wanted in $this->server for these
                $this->server[$key] = $this->http[$nicekey];
                
                // disallow external setting of X-JSON headers.
                if ($nicekey == 'x-json') {
                    unset($this->http[$nicekey]);
                    unset($this->server[$key]);
                }
            }
        }
    }
    
    /**
     * 
     * Common method to get a request value and return it.
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
            return $this->$var;
        } elseif (array_key_exists($key, $this->$var)) {
            // found the requested key.
            // need the funny {} becuase $var[$key] will try to find a
            // property named for that element value, not for $var.
            return $this->{$var}[$key];
        } else {
            // requested key does not exist
            return $alt;
        }
    }
}
