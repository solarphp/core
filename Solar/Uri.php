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
	* Imports the current URI string.
	* 
	* @access public
	* 
	* @param string $uri The URI to parse; if null, defaults to the current
	* $this->config['uri'].
	* 
	* @return array An array of all the URI component parts.
	* 
	*/
	
	public function import($uri = null)
	{
		// build a default scheme (with '://' in it)
		$ssl = Solar::super('server', 'HTTPS', 'off');
		$scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
		
		// we'll parse the modified uri, not the original as passed.
		$modified_uri = $uri;
		
		// force to the current uri?
		if (! $uri) {
			$modified_uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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
		
		// touchup
		if ($uri) {
		
			// a uri string was passed; parse query elements into an array.
			parse_str($elem['query'], $elem['query']);
			
		} else {
		
			// force the query string
			$elem['query'] = Solar::get();
			
			// force the path to the script
			$elem['path'] = Solar::super('server', 'SCRIPT_NAME');
			
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
	* Builds the parsed URI elements into a string.
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
		$uri .= empty($this->info)     ? '' : '/' . implode('/', $this->info);
		$uri .= empty($this->query)    ? '' :  '?' . self::query2str($this->query);
		$uri .= empty($this->fragment) ? '' :  '#' . $this->fragment;
		
		// done!
		return $uri;
	}
	
	
	/**
	* 
	* Builds an array of URI elements into a string.
	* 
	* Modified from code written by nospam@fiderallalla.de, found at
	* http://php.net/parse_str.
	* 
	* @access public
	* 
	* @return string A URI query string.
	* 
	*/
	
	public static function query2str($params)
	{
		if (! is_array($params) || count($params) == 0 ) {
			return '';
		}
		
		$args = func_get_args();
		$akey = (! isset($args[1])) ? false : $args[1];       
		$out = array();
		
		foreach ($params as $key => $val) {
			if (is_array($val) ) {   
				$out[] = self::query2str($val, $key);
				continue;
			}
			
			$thekey = (! $akey) ? $key : $akey.'['.$key.']';
			$out[] = $thekey . '=' . $val;
		}
		
		return implode('&', $out);   
	}
	
	
	/**
	* 
	* Changes the elements and values in the $this->query array.
	* 
	* With this method, you can 'add', 'set' or 'del' (delete) a value. 
	* You can also use the 'setstr' action to set the query elements
	* from a URI query string as the $key parameter.
	* 
	* When using 'add', the element will be converted to an array if 
	* there is more than one value.
	* 
	* @access public
	* 
	* @param string $act The action to take: 'add', 'set', 'setstr', or 'del'.
	* 
	* @param string $key The GET variable name to work with.
	* 
	* @param string $val The value to use.
	* 
	* @return void
	* 
	*/
	
	public function query($act, $key, $val = '')
	{
		switch (strtolower($act)) {
		
		case 'add':
			if (isset($this->query[$key])) {
				$this->query[$key][] = $val;
			} else {
				$this->query[$key] = $val;
			}
			break;
		
		case 'set':
			$this->query[$key] = $val;
			break;
		
		case 'setstr':
			parse_str($key, $this->query);
			break;
			
		case 'del':
			unset($this->query[$key]);
			break;
		}
	}
	
	
	/**
	* 
	* Changes the elements and values in the $this->info array.
	* 
	* With this method, you can 'set' or 'del' (delete) a value. 
	* You can also use the 'setstr' action to set the path_info
	* from a URI query string as the $key parameter.
	* 
	* @access public
	* 
	* @param string $act The action to take: 'add', 'setstr', or 'del'.
	* 
	* @param string $key The path_info position to work with.
	* 
	* @param string $val The value to use.
	* 
	* @return void
	* 
	*/
	
	public function info($act, $key, $val = '')
	{
		switch (strtolower($act)) {
		
		case 'set':
			$this->info[(int)$key] = $val;
			break;
		
		case 'setstr':
			$this->info = explode('/', $key);
			if (substr($key, 0, 1) == '/') {
				array_shift($this->info);
			}
			break;
			
		case 'del':
			unset($this->info[(int)$key]);
			break;
		}
	}
	
	
	/**
	* 
	* Clears (resets) $this->info to a blank array.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function clearInfo()
	{
		$this->info = array();
	}
	
	
	/**
	* 
	* Clears (resets) $this->query to a blank array.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function clearQuery()
	{
		$this->query = array();
	}
}
?>