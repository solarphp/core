<?php
/**
 * 
 * Manipulates and generates URI strings.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Manipulates and generates URI strings.
 * 
 * This class helps you to create and manipulate URIs, including query
 * strings and path elements. It does so by splitting up the pieces of the
 * URI and allowing you modify them individually; you can then then fetch
 * them as a single URI string. This helps when building complex links,
 * such as in a paged navigation system.
 * 
 * > Note: For controller action URIs, use [[Class::Solar_Uri_Action | ]].
 * > Likewise, for public resource URIs, use [[Class::Solar_Uri_Public | ]].
 * 
 * The following is a simple example. Say that the page address is currently
 * `http://anonymous::guest@example.com/path/to/index.php/foo/bar?baz=dib#anchor`.
 * 
 * You can use Solar_Uri to parse this complex string very easily:
 * 
 * {{code: php
 *     require_once 'Solar.php';
 *     Solar::start();
 * 
 *     // create a URI object; this will automatically import the current
 *     // location, which is...
 *     // 
 *     // http://anonymous:guest@example.com/path/to/index.php/foo/bar.xml?baz=dib#anchor
 *     $uri = Solar::factory('Solar_Uri');
 * 
 *     // now the $uri properties are ...
 *     // 
 *     // $uri->scheme   => 'http'
 *     // $uri->host     => 'example.com'
 *     // $uri->user     => 'anonymous'
 *     // $uri->pass     => 'guest'
 *     // $uri->path     => array('path', 'to', 'index.php', 'foo', 'bar')
 *     // $uri->format   => 'xml'
 *     // $uri->query    => array('baz' => 'dib')
 *     // $uri->fragment => 'anchor'
 * }}
 * 
 * Now that we have imported the URI and had it parsed automatically, we
 * can modify the component parts, then fetch a new URI string.
 * 
 * {{code: php
 *     // change to 'https://'
 *     $uri->scheme = 'https';
 * 
 *     // remove the username and password
 *     $uri->user = '';
 *     $uri->pass = '';
 * 
 *     // change the value of 'baz' to 'zab'
 *     $uri->setQuery('baz', 'zab');
 * 
 *     // add a new query element called 'zim' with a value of 'gir'
 *     $uri->query['zim'] = 'gir';
 * 
 *     // reset the path to something else entirely.
 *     // this will additionally set the format to 'php'.
 *     $uri->setPath('/something/else/entirely.php');
 * 
 *     // add another path element
 *     $uri->path[] = 'another';
 *     
 *     // and fetch it to a string.
 *     $new_uri = $uri->fetch();
 * 
 *     // the $new_uri string is as follows; notice how the format
 *     // is always applied to the last path-element.
 *     // /something/else/entirely/another.php?baz=zab&zim=gir#anchor
 * 
 *     // wait, there's no scheme or host!
 *     // we need to fetch the "full" URI.
 *     $full_uri = $uri->fetch(true);
 * 
 *     // the $full_uri string is:
 *     // https://example.com/something/else/entirely/another.php?baz=zab&zim=gir#anchor
 * }}
 * 
 * 
 * This class has a number of public properties, all related to
 * the parsed URI processed by [[Solar_Uri::set()]]. They are ...
 * 
 * | Name       | Type    | Description
 * | ---------- | ------- | --------------------------------------------------------------
 * | `schema`   | string  | The schema protocol; e.g.: http, https, ftp, mailto
 * | `host`     | string  | The host name; e.g.: example.com
 * | `port`     | string  | The port number
 * | `user`     | string  | The username for the URI
 * | `pass`     | string  | The password for the URI
 * | `path`     | array   | A sequential array of the path elements
 * | `format`   | string  | The filename-extension indicating the file format
 * | `query`    | array   | An associative array of the query terms
 * | `fragment` | string  | The anchor or page fragment being addressed
 * 
 * As an example, the following URI would parse into these properties:
 * 
 *     http://anonymous:guest@example.com:8080/foo/bar.xml?baz=dib#anchor
 *     
 *     schema   => 'http'
 *     host     => 'example.com'
 *     port     => '8080'
 *     user     => 'anonymous'
 *     pass     => 'guest'
 *     path     => array('foo', 'bar')
 *     format   => 'xml'
 *     query    => array('baz' => 'dib')
 *     fragment => 'anchor'
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
     * Keys are ...
     * 
     * `path`
     * : (string) A path prefix.  Generally needed only
     *   for specific URI subclasses, for example Solar_Uri_Action.
     * 
     * `uri`
     * : (string) Call set() with this URI string at construct-time, instead
     *   of loading from the current URI.
     * 
     * @var array
     * 
     */
    protected $_Solar_Uri = array(
        'path' => '',
        'uri'  => null,
    );
    
    /**
     * 
     * The scheme (for example 'http' or 'https').
     * 
     * @var string
     * 
     */
    public $scheme = null;
    
    /**
     * 
     * The host specification (for example, 'example.com').
     * 
     * @var string
     * 
     */
    public $host = null;
    
    /**
     * 
     * The port number (for example, '80').
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
     * The path portion (for example, 'path/to/index.php').
     * 
     * @var array
     * 
     */
    public $path = null;
    
    /**
     * 
     * The dot-format extension of the last path element (for example, the "rss"
     * in "feed.rss").
     * 
     * @var string
     * 
     */
    public $format = null;
    
    /**
     * 
     * Query string elements split apart into an array.
     * 
     * @var array
     * 
     */
    public $query = array();
    
    /**
     * 
     * The fragment portion (for example, the "foo" in "#foo").
     * 
     * @var string
     * 
     */
    public $fragment = null;
    
    /**
     * 
     * Url-encode only these characters in path elements.
     * 
     * Characters are ' ' (space), '/', '?', '&', and '#'.
     * 
     * @var array
     * 
     */
    protected $_encode_path = array (
        ' ' => '+',
        '/' => '%2F',
        '?' => '%3F',
        '&' => '%26',
        '#' => '%23',
    );
    
    /**
     * 
     * Details about the request environment.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
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
        
        // get the request environment
        $this->_request = Solar::factory('Solar_Request');
        
        // fix the base path by adding leading and trailing slashes
        if (trim($this->_config['path']) == '') {
            $this->_config['path'] = '/';
        }
        if ($this->_config['path'][0] != '/') {
            $this->_config['path'] = '/' . $this->_config['path'];
        }
        $this->_config['path'] = rtrim($this->_config['path'], '/') . '/';
        
        // set properties
        $this->set($this->_config['uri']);
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
        $ssl = $this->_request->server('HTTPS', 'off');
        $scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
        
        // get the current host, using a dummy host name if needed.
        // we need a host name so that parse_url() works properly.
        // we remove the dummy host name at the end of this method.
        $host = $this->_request->server('HTTP_HOST', 'example.com');
        
        // right now, we assume we don't have to force any values.
        $forced = false;
        
        // forcibly set to the current uri?
        $uri = trim($uri);
        if (! $uri) {
            
            // we're forcing values
            $forced = true;
            
            // add the scheme and host
            $uri = $scheme . $host;
            
            // we need to see if mod_rewrite is turned on or off.
            // if on, we can use REQUEST_URI as-is.
            // if off, we need to use the script name, esp. for
            // front-controller stuff.
            // we make a guess based on the 'path' config key.
            // if it ends in '.php' then we guess that mod_rewrite is
            // off.
            if (substr($this->_config['path'], -5) == '.php/') {
                // guess that mod_rewrite is off; build up from 
                // component parts.
                $uri .= $this->_request->server('SCRIPT_NAME')
                      . $this->_request->server('PATH_INFO')
                      . '?' . $this->_request->server('QUERY_STRING');
            } else {
                // guess that mod_rewrite is on
                $uri .= $this->_request->server('REQUEST_URI');
            }
        }
        
        // forcibly add the scheme and host?
        $pos = strpos($uri, '://');
        if ($pos === false) {
            $forced = true;
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
        // the conditions are ...
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
        
        // extended processing of parsed elements into properties
        $this->setPath($elem['path']); // will also set $this->format
        $this->setQuery($elem['query']);
        
        // if we had to force values, remove dummy placeholders
        if ($forced && ! $this->_request->server('HTTP_HOST')) {
            $this->scheme = null;
            $this->host = null;
        }
    }
    
    /**
     * 
     * Returns a URI based on the object properties.
     * 
     * @param bool $full If true, returns a full URI with scheme,
     * user, pass, host, and port.  Otherwise, just returns the
     * path, format, query, and fragment.  Default false.
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
            $uri .= empty($this->scheme) ? '' : urlencode($this->scheme) . '://';
        
            // add the username and password, if any.
            if (! empty($this->user)) {
                $uri .= urlencode($this->user);
                if (! empty($this->pass)) {
                    $uri .= ':' . urlencode($this->pass);
                }
                $uri .= '@';
            }
        
            // add the host and port, if any.
            $uri .= (empty($this->host) ? '' : urlencode($this->host))
                  . (empty($this->port) ? '' : ':' . (int) $this->port);
        }
        
        // add the rest of the URI
        return $uri
             . $this->_config['path']
             . (empty($this->path)     ? '' : $this->_pathEncode($this->path))
             . (empty($this->format)   ? '' : '.' . urlencode($this->format))
             . (empty($this->query)    ? '' : '?' . http_build_query($this->query))
             . (empty($this->fragment) ? '' : '#' . urlencode($this->fragment));
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
        parse_str($spec, $tmp);
        if (get_magic_quotes_gpc()) {
            $this->query = array();
            foreach ($tmp as $key => $val) {
                $key = stripslashes($key);
                $val = stripslashes($val);
                $this->query[$key] = $val;
            }
        } else {
            $this->query = $tmp;
        }
    }
    
    /**
     * 
     * Sets the Solar_Uri::$path array from a string.
     * 
     * This will overwrite any previous values. Also, resets the format based
     * on the final path value.
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
        $this->_setFormatFromPath();
    }
    
    /**
     * 
     * Removes and stores any trailing .format extension of last path element.
     * 
     * @return void
     * 
     */
    protected function _setFormatFromPath()
    {
        $this->format = null;
        $val = end($this->path);
        if ($val) {
            // find the last dot in the value
            $pos = strrpos($val, '.');
            if ($pos !== false) {
                $key = key($this->path);
                $this->format = substr($val, $pos + 1);
                $this->path[$key] = substr($val, 0, $pos);
            }
        }
    }
    
    /**
     * 
     * Converts an array of path elements into a string.
     * 
     * Does not use [[php::urlencode() | ]]; instead, only converts
     * characters found in Solar_Uri::$_encode_path.
     * 
     * @param array $spec The path elements.
     * 
     * @return string A URI path string.
     * 
     */
    protected function _pathEncode($spec)
    {
        if (is_string($spec)) {
            $spec = explode('/', $spec);
        }
        $keys = array_keys($this->_encode_path);
        $vals = array_values($this->_encode_path);
        $out = array();
        foreach ((array) $spec as $elem) {
            $out[] = str_replace($keys, $vals, $elem);
        }
        return implode('/', $out);
    }
}
