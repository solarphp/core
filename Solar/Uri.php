<?php
/**
 * 
 * Manipulates URI properties.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
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
 * Manipulates URI properties.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 */
class Solar_Uri extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * : \\path\\ : (string) A path prefix, e.g. '/index.php/'.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'path' => null,
    );
    
    /**
     * 
     * The scheme (e.g. 'http' or 'https').
     * 
     * @var string
     * 
     */
    public $scheme = null;
    
    /**
     * 
     * The host specification (e.g., 'example.com').
     * 
     * @var string
     * 
     */
    public $host = null;
    
    /**
     * 
     * The port number (e.g., '80').
     * 
     * @var string
     * 
     */
    public $port = null;
    
    /**
     * 
     * The username, if any.
     * 
     * @var string
     * 
     */
    public $user = null;
    
    /**
     * 
     * The password, if any.
     * 
     * @var string
     * 
     */
    public $pass = null;
    
    /**
     * 
     * The path portion (e.g., 'path/to/index.php').
     * 
     * @var string
     * 
     */
    public $path = null;
    
    /**
     * 
     * Query string elements split apart into an array.
     * 
     * @var string
     * 
     */
    public $query = array();
    
    /**
     * 
     * The fragment portion (e.g., "#subsection").
     * 
     * @var string
     * 
     */
    public $fragment = null;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // real construction
        parent::__construct($config);
        
        // fix the prefix by adding leading and trailing slashes
        if ($this->_config['path'][0] != '/') {
            $this->_config['path'] = '/' . $this->_config['path'];
        }
        $this->_config['path'] = rtrim($this->_config['path'], '/') . '/';
        
        // set properties
        $this->set();
    }
    
    /**
     * 
     * Sets properties from a specified URI.
     * 
     * @param string $uri The URI to parse.  If null, defaults to the
     * current URI.
     * 
     * @return void
     * 
     */
    public function set($uri = null)
    {
        // build a default scheme (with '://' in it)
        $ssl = Solar::server('HTTPS', 'off');
        $scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
        
        // get the current host
        $host = Solar::server('HTTP_HOST');
        
        // force to the current uri?
        $uri = trim($uri);
        if (! $uri) {
            $uri = $scheme . $host . Solar::server('REQUEST_URI');
        }
        
        // add the scheme and host?
        $pos = strpos($uri, '://');
        if ($pos === false) {
            $uri = ltrim($uri, '/');
            $uri = "$scheme$host/$uri";
        }
        
        // default uri elements
        $elem = array(
            'scheme'   => null,
            'user'     => null,
            'pass'     => null,
            'host'     => null,
            'port'     => null,
            'path'     => null,
            'query'    => null,
            'fragment' => null,
        );
        
        // parse the uri and merge with the defaults
        $elem = array_merge($elem, parse_url($uri));
        
        // strip the prefix from the path.
        // the conditions are:
        // $elem['path'] == '/index.php/'
        // -- or --
        // $elem['path'] == '/index.php'
        // -- or --
        // $elem['path'] == '/index.php/*'
        //
        $path = $this->_config['path'];
        $len  = strlen($path);
        $flag = $elem['path'] == $path ||
                $elem['path'] == rtrim($path, '/') ||
                substr($elem['path'], 0, $len) == $path;
            
        if ($flag) {
            $elem['path'] = substr($elem['path'], $len);
        }
        
        // retain parsed elements as properties
        $this->scheme   = $elem['scheme'];
        $this->user     = $elem['user'];
        $this->pass     = $elem['pass'];
        $this->host     = $elem['host'];
        $this->port     = $elem['port'];
        $this->fragment = $elem['fragment'];
        $this->setPath($elem['path']);
        $this->setQuery($elem['query']);
    }
    
    /**
     * 
     * Returns a URI based on the object properties.
     * 
     * @param bool $full If true, returns a full URI with scheme,
     * user, pass, host, and port.  Otherwise, just returns the
     * path, query, and fragment.  Default false.
     * 
     * @return string An action URI string.
     * 
     */
    public function fetch($full = false)
    {
        // the uri string
        $uri = '';
        
        // are we doing a full URI?
        if ($full) {
            
            // add the scheme, if any.
            $uri .= empty($this->scheme) ? '' : $this->scheme . '://';
        
            // add the username and password, if any.
            if (! empty($this->user)) {
                $uri .= $this->user;
                if (! empty($this->pass)) {
                    $uri .= ':' . $this->pass;
                }
                $uri .= '@';
            }
        
            // add the host and port, if any.
            $uri .= (empty($this->host) ? '' : $this->host)
                  . (empty($this->port) ? '' : ':' . $this->port);
        }
        
        // add the rest of the URI
        return $uri
             . $this->_config['path']
             . (empty($this->path)     ? '' : $this->_path2str($this->path))
             . (empty($this->query)    ? '' : '?' . $this->_query2str($this->query))
             . (empty($this->fragment) ? '' : '#' . $this->fragment);
    }
    
    /**
     * 
     * Returns a URI based on the specified string.
     * 
     * @param string $spec The URI specification.
     * 
     * @param bool $full If true, returns a full URI with scheme,
     * user, pass, host, and port.  Otherwise, just returns the
     * path, query, and fragment.  Default false.
     * 
     * @return string An action URI string.
     * 
     */
    public function quick($spec, $full = false)
    {
        $uri = clone($this);
        $uri->set($spec);
        return $uri->fetch($full);
    }
    
    
    /**
     * 
     * Sets the Solar_Uri::$query array from a string.
     * 
     * This will overwrite any previous values.
     * 
     * @param string $spec The query string to use; for example,
     * "foor=bar&baz=dib".
     * 
     * @return void
     * 
     */
    public function setQuery($spec)
    {
        parse_str($spec, $this->query);
    }
    
    /**
     * 
     * Sets the Solar_Uri::$path array from a string.
     * 
     * This will overwrite any previous values.
     * 
     * @param string $spec The path string to use; for example,
     * "/foo/bar/baz/dib".  A leading slash will *not* create an empty
     * first element; if the string has a leading slash, it is ignored.
     * 
     * @return void
     * 
     */
    public function setPath($spec)
    {
        $this->path = explode('/', trim($spec, '/'));
        foreach ($this->path as $key => $val) {
            $this->path[$key] = urldecode($val);
        }
    }
    
    /**
     * 
     * Converts an array of query elements into a string.
     * 
     * Modified from code written by nospam@fiderallalla.de, found at
     * http://php.net/parse_str.  Automatically urlencodes values.
     * 
     * @param array $spec The key-value pairs to convert into a
     * query string.
     * 
     * @param string $key The parent key for the current array.
     * 
     * @return string A URI query string.
     * 
     */
    protected function _query2str($spec)
    {
        // preempt if $spec is not an array, or is empty
        if (! is_array($spec) || count($spec) == 0 ) {
            return '';
        }
        
        $args = func_get_args();
        
        // is there an array key present?
        $akey = (! isset($args[1])) ? false : $args[1];       
        
        // the array of generated query substrings
        $out = array();
        
        foreach ($spec as $key => $val) {
            if (is_array($val) ) {   
                // recurse to capture deeper array.
                $out[] = $this->_query2str($val, $key);
            } else {
                // not an array, use the current value.
                $thekey = (! $akey) ? $key : $akey.'['.$key.']';
                $out[] = urlencode($thekey) . '=' . urlencode($val);
            }
        }
        
        return implode('&', $out);
    }
    
    /**
     * 
     * Converts an array of path elements into a string.
     * 
     * @param array $spec The path elements.
     * 
     * @return string A URI path string.
     * 
     */
    protected function _path2str($spec)
    {
        settype($spec, 'array');
        $out = array();
        foreach ($spec as $val) {
            $out[] = urlencode($val);
        }
        return implode('/', $out);
    }
}
?>