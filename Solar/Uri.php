<?php

/**
* 
* Parses a URI string into its component parts for manipulation and export.
* 
* @category Solar
* 
* @package Solar
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
* @package Solar
* 
* @todo add a way to let import() know what the script name is
* 
*/

class Solar_Uri extends Solar_Base {
	
	
	/**
	* 
	* The scheme (e.g. 'http' or 'https').
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $scheme = null;
	
	
	/**
	* 
	* The host specification (e.g., 'example.com').
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $host = null;
	
	
	/**
	* 
	* The port number (e.g., '80').
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $port = null;
	
	
	/**
	* 
	* The username, if any.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $user = null;
	
	
	/**
	* 
	* The password, if any.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $pass = null;
	
	
	/**
	* 
	* The path portion (e.g., 'path/to/index.php').
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $path = null;
	
	
	/**
	* 
	* Path info elements after the script name (from $_SERVER['PATH_INFO']).
	* 
	* This will not work when importing URIs from text, because there's
	* no good way to know what portion of the path is the script, and
	* what portion is the info.  The only time it works is when
	* importing the current URI (i.e., when the only import() parameter
	* is empty).
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $info = array();
	
	
	/**
	* 
	* Query string elements split apart into an array.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $query = array();
	
	
	/**
	* 
	* The fragment portion (e.g., "#subsection").
	* 
	* @access public
	* 
	* @var string
	* 
	* @todo Bug in parse_url (PHP 5.0.3) does not find fragments.
	*/
	
	public $fragment = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
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
	* @access public
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
		} elseif (strpos('://', $uri) === false) {
			// add the scheme
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
			'fragment' => null // bug in php 5.0.3 does not find fragments!
		);
		
		// parse the uri and merge with the defaults
		$elem = array_merge($elem, parse_url($modified_uri));
		
		// touchup; was a uri passed in the first place?
		if ($uri) {
		
			// a uri string was passed; parse query elements into an array.
			// note that we do not capture path_info, as there's no way
			// to tell where it is (we would need the script name).
			parse_str($elem['query'], $elem['query']);
			
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
	* Builds the object URI properties into a string.
	* 
	* @access public
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
		$uri .= empty($this->host)     ? '' : $this->host;
		$uri .= empty($this->port)     ? '' : ':' . $this->port;
		$uri .= empty($this->path)     ? '' : $this->path;
		$uri .= empty($this->info)     ? '' : '/' . $this->info2str($this->info);
		$uri .= empty($this->query)    ? '' :  '?' . $this->query2str($this->query);
		$uri .= empty($this->fragment) ? '' :  '#' . $this->fragment;
		
		// done!
		return $uri;
	}
	
	
	/**
	* 
	* Adds an element to the $this->query array.
	* 
	* If the element already exists, the element is converted to an array
	* and the value is appended to that array.
	*  
	* @access public
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
	* Sets the value of an element in the $this->query array.
	* 
	* This will overwrite any previous value.
	* 
	* @access public
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
	* @access public
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
	* Sets one element in the $this->info array by position and value.
	* 
	* @access public
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
	* @access public
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
		$this->info = explode('/', $val);
		if (substr($val, 0, 1) == '/') {
			array_shift($this->info);
		}
	}
	
	
	/**
	* 
	* Clears (resets) all or part of $this->info.
	* 
	* @access public
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
	* @access public
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
	* @access protected
	* 
	* @param array $params The key-value pairs to convert into a
	* query string.
	* 
	* @return string A URI query string.
	* 
	*/
	
	protected function query2str($params)
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
				$out[] = $this->query2str($val, $key);
			} else {
				// not an array, use the current value.
				$thekey = (! $akey) ? $key : $akey.'['.$key.']';
				$out[] = $thekey . '=' . urlencode($val);
			}
		}
		
		return implode('&', $out);
	}
	
	
	/**
	* 
	* Converts an array of info elements into a string.
	* 
	* @access protected
	* 
	* @param array $params The pathinfo elements.
	* 
	* @return string A URI pathinfo string.
	* 
	*/
	
	protected function info2str($params)
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