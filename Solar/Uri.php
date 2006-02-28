<?php
/**
 * 
 * Parses a URI string into its component parts for manipulation and export.
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
 * Parses a URI string into its component parts for manipulation and export.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 * @todo add a way to let import() know what the script name is
 * 
 * @todo add a way to let actionImport() and actionExport() know what the
 * front controller path is
 * 
 * @todo convert "%2B" to "+" on export? of course, that means real
 * plus-signs will be spaces...
 * 
 */
class Solar_Uri extends Solar_Base {
    
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
     * Path info elements after the script name (from $_SERVER['PATH_INFO']).
     * 
     * The import() method attempts to guess the script name as the
     * first '*.php' part of the path, and counts elements after that
     * as path info.
     * 
     * @var array
     * 
     */
    public $info = array();
    
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
        parent::__construct($config);
        $this->import();
    }
    
    /**
     * 
     * Imports a URI string (by default, the current URI) into the object.
     * 
     * @param string $uri The URI to parse.  If null, defaults to the
     * current URI, and retrives path_info values; if not null, cannot
     * retrieve path_info values.
     * 
     * @return void
     * 
     */
    public function import($uri = null)
    {
        // build a default scheme (with '://' in it)
        $ssl = Solar::server('HTTPS', 'off');
        $scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
        
        // we'll parse the modified uri, not the original as passed.
        $modified_uri = $uri;
        
        // force to the current uri?
        if (! $uri) {
            $modified_uri = $scheme . Solar::server('HTTP_HOST');
            $modified_uri .= Solar::server('REQUEST_URI');
        } elseif (strpos($uri, '://') === false) {
            // there's a uri, but need to add the scheme
            $modified_uri = $scheme . $uri;
        }
        
        // default elements
        $elem = array(
            'scheme'   => null,
            'host'     => null,
            'port'     => null,
            'user'     => null,
            'pass'     => null,
            'path'     => null,
            'info'     => array(),
            'query'    => array(),
            'fragment' => null,
        );
        
        // parse the uri and merge with the defaults
        $elem = array_merge($elem, parse_url($modified_uri));
        
        // touchup; was a uri passed in the first place?
        if ($uri) {
            
            // a uri string was passed; try to capture path_info by guessing.
            $orig = explode('/', trim($elem['path'], '/'));
            $path = array();
            
            while (! empty($orig)) {
                $val = array_shift($orig);
                $path[] = $val;
                if (stripos($val, '.php') !== false) {
                    // this is the script.php part;
                    // keep it and drop out
                    break;
                } 
            };
            
            $elem['path'] = '/' . implode('/', $path);
            $elem['info'] = $orig;
            
            // now capture the query portions.
            if (! empty($elem['query'])) {
                parse_str($elem['query'], $elem['query']);
            }
            
        } else {
            
            // no uri was passed, so use the current settings instead.
            // force the query string
            $elem['query'] = Solar::get();
            
            // force the path to the script
            $elem['path'] = Solar::server('SCRIPT_NAME');
            
            //  get path info
            $elem['info'] = Solar::pathinfo();
        }
        
        // done, pass into the properties
        foreach ($elem as $key => $val) {
            $this->$key = $val;
        }
    }
    
    /**
     * 
     * Parses a Solar_Controller action spec as a URI.
     * 
     * @return void
     * 
     */
    public function importAction($spec)
    {
        // make sure there's actually an action spec,
        // forcibly add a leading slash
        $spec = trim($spec);
        if (! $spec) {
            $spec = '/';
        }
        
        // build a URI string with a fake host and path
        $fake = 'fake.com/fake.php';
        if ($spec[0] != '/') {
            $fake .= '/';
        }
        $fake .= $spec;
        
        // import the fake uri, then remove the fake host and path
        $this->import($fake);
        $this->host = null;
        $this->path = null;
    }
    
    /**
     * 
     * Builds a full URI string.
     * 
     * @return string A URI string.
     * 
     */
    public function export()
    {
        // build the uri as we go.
        // add the scheme.
        $uri = empty($this->scheme) ? '' : $this->scheme . '://';
        
        // add the username and password, if any.
        if (! empty($this->user)) {
            $uri .= $this->user;
            if (! empty($this->pass)) {
                $uri .= ':' . $this->pass;
            }
            $uri .= '@';
        }
        
        // add the remaining pieces.
        $uri .= (empty($this->host)     ? '' : $this->host)
              . (empty($this->port)     ? '' : ':' . $this->port)
              . (empty($this->path)     ? '' : $this->path)
              . (empty($this->info)     ? '' : '/' . $this->_info2str($this->info))
              . (empty($this->query)    ? '' : '?' . $this->_query2str($this->query))
              . (empty($this->fragment) ? '' : '#' . $this->fragment);
        
        // done!
        return $uri;
    }
    
    /**
     * 
     * Builds a partial URI string for a Solar_Controller action spec.
     * 
     * @return string A URI string.
     * 
     */
    public function exportAction()
    {
        return (empty($this->info)     ? '' : $this->_info2str($this->info))
             . (empty($this->query)    ? '' : '?' . $this->_query2str($this->query))
             . (empty($this->fragment) ? '' : '#' . $this->fragment);
    }
    
    /**
     * 
     * Adds an element to the $this->query array.
     * 
     * If the element already exists, the element is converted to an array
     * and the value is appended to that array.
     *  
     * @param string $key The GET variable name to work with.
     * 
     * @param string $val The value to use.
     * 
     * @return void
     * 
     */
    public function addQuery($key, $val = '')
    {
        if (isset($this->query[$key])) {
            settype($this->query[$key], 'array');
            $this->query[$key][] = $val;
        } else {
            $this->query[$key] = $val;
        }
    }
    
    /**
     * 
     * Clears all URI properties.
     * 
     * @return void
     * 
     */
    public function clear()
    {
        $this->scheme = null;
        $this->host = null;
        $this->port = null;
        $this->user = null;
        $this->pass = null;
        $this->path = null;
        $this->info = array();
        $this->query = array();
        $this->fragment = null;
    }
    
    /**
     * 
     * Sets the value of an element in the $this->query array.
     * 
     * This will overwrite any previous value.
     * 
     * @param string $key The GET variable name to work with.
     * 
     * @param string $val The value to use.
     * 
     * @return void
     * 
     */
    public function setQuery($key, $val = '')
    {
        $this->query[$key] = $val;
    }
    
    /**
     * 
     * Sets all elements of $this->query from a query string.
     * 
     * This will overwrite any previous values.
     * 
     * @param string $val The query string to set from; for example,
     * "foo=bar&baz=dib&zim=gir".
     * 
     * @return void
     * 
     */
    public function setQueryString($val)
    {
        parse_str($val, $this->query);
    }
    
    /**
     * 
     * Adds one element to the $this->info array.
     * 
     * @param string $val The value to use.
     * 
     * @return void
     * 
     */
    public function addInfo($val = '')
    {
        $this->info[] = $val;
    }
    
    /**
     * 
     * Sets one element in the $this->info array by position and value.
     * 
     * @param int $key The path_info position to work with.
     * 
     * @param string $val The value to use.
     * 
     * @return void
     * 
     */
    public function setInfo($key, $val = '')
    {
        $this->info[(int)$key] = $val;
    }
    
    /**
     * 
     * Sets all elements in the $this->info array from a path_info string.
     * 
     * This will overwrite any previous values.
     * 
     * @param string $val The path_info string to use; for example,
     * "/foo/bar/baz/dib".  A leading slash will *not* create an empty
     * first element; if the string has a leading slash, it is ignored.
     * 
     * @return void
     * 
     */
    public function setInfoString($val)
    {
        $val = trim($val, '/');
        $this->info = explode('/', $val);
    }
    
    /**
     * 
     * Clears (resets) all or part of $this->info.
     * 
     * @return void
     * 
     */
    public function clearInfo($key = null)
    {
        if ($key === null || $key === false) {
            $this->info = array();
        } elseif (array_key_exists((int) $key, $this->info)) {
            unset($this->info[(int) $key]);
        }
    }
    
    /**
     * 
     * Clears (resets) all or part of $this->query.
     * 
     * @return void
     * 
     */
    public function clearQuery($key = null)
    {
        if (! $key) {
            $this->query = array();
        } else {
            unset($this->query[$key]);
        }
    }
    
    /**
     * 
     * Converts an array of query elements into a string.
     * 
     * Modified from code written by nospam@fiderallalla.de, found at
     * http://php.net/parse_str.  Automatically urlencodes values.
     * 
     * @param array $params The key-value pairs to convert into a
     * query string.
     * 
     * @return string A URI query string.
     * 
     */
    protected function _query2str($params)
    {
        // preempt if $params is not an array, or is empty
        if (! is_array($params) || count($params) == 0 ) {
            return '';
        }
        
        $args = func_get_args();
        
        // is there an array key present?
        $akey = (! isset($args[1])) ? false : $args[1];       
        
        // the array of generated query substrings
        $out = array();
        
        foreach ($params as $key => $val) {
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
     * Converts an array of info elements into a string.
     * 
     * @param array $params The pathinfo elements.
     * 
     * @return string A URI pathinfo string.
     * 
     */
    protected function _info2str($params)
    {
        settype($params, 'array');
        $str = array();
        foreach ($params as $val) {
            $str[] = urlencode($val);
        }
        return implode('/', $str);
    }
}
?>