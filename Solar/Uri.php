<?php

// @todo finish conversion from static to instance

class Solar_Uri extends Solar_Base {
	
	// the parsed pieces of the the URI
	public $elem = array(
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
	
	public function import()
	{
		// build a default scheme (with '://' in it)
		$ssl = Solar::super('server', 'HTTPS', 'off');
		$scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
		
		// the current uri
		$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
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
		$elem = array_merge($elem, parse_url($uri));
		
		// force the query string
		$elem['query'] = Solar::get();
		
		// force the path to the script
		$elem['path'] = Solar::super('server', 'SCRIPT_NAME');
		
		//  get path info
		$elem['info'] = Solar::pathinfo();
		
		// done!
		$this->elem = $elem;
	}
	
	
	/**
	* 
	* Builds an array of URI elements into a string.
	* 
	* @access public
	* 
	* @return string A URI string.
	* 
	*/
	
	public function export()
	{
		// add the scheme
		$uri = empty($this->elem['scheme']) ? '' : $this->elem['scheme'] . '://';
		
		// add the username and password
		if (! empty($this->elem['user'])) {
			$uri .= $this->elem['user'];
			if (isset($this->elem['pass'])) {
				$uri .= ':' . $this->elem['pass'];
			}
			$uri .= '@';
		}
		
		// add the remaining pieces
		$uri .= empty($this->elem['host'])     ? '' : $this->elem['host'];
		$uri .= empty($this->elem['port'])     ? '' : ':' . $this->elem['port'];
		$uri .= empty($this->elem['path'])     ? '' : $this->elem['path'];
		$uri .= empty($this->elem['info'])     ? '' : '/' . implode('/', $this->elem['info']);
		$uri .= empty($this->elem['query'])    ? '' :  '?' . self::query2str($this->elem['query']);
		$uri .= empty($this->elem['fragment']) ? '' :  '#' . uriencode($this->elem['fragment']);
		
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
	* Changes the path_info values in the parsed URI.
	* 
	* With this method, you can 'set' or 'del' (delete) a value.
	* 
	* @access public
	* 
	* @param string $act The action to take: 'set' or 'del'.
	* 
	* @param string $pos The path-info position to work with.
	* 
	* @param string $val The value to use.
	* 
	*/
	
	public function query($act, $key, $val = '')
	{
		switch (strtolower($act)) {
		
		case 'set':
			$this->elem['query'][$key] = $val;
			break;
		
		case 'setstr':
			parse_str($key, $this->elem['query']);
			break;
			
		case 'add':
			if (isset($this->elem['query'][$key])) {
				$this->elem['query'][$key][] = $val;
			} else {
				$this->elem['query'][$key] = $val;
			}
			break;
		
		case 'del':
			unset($this->elem['query'][$key]);
			break;
		}
	}
	
	public function info($act, $key, $val = '')
	{
		switch (strtolower($act)) {
		
		case 'set':
			$this->elem['info'][$key] = $val;
			break;
		
		case 'setstr':
			$this->elem['info'] = explode('/', $key);
			if (substr($key, 0, 1) == '/') {
				array_shift($this->elem['info']);
			}
			break;
			
		case 'del':
			unset($this->elem['info'][$key]);
			break;
		}
	}
	
	public function clearInfo()
	{
		$this->elem['info'] = array();
	}
	
	public function clearQuery()
	{
		$this->elem['query'] = array();
	}
}
?>