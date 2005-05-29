<?php

/**
* 
* Provides an object-oriented template system for PHP5.
* 
* Savant3 helps you separate business logic from presentation logic
* using PHP as the template language. By default, Savant3 does not
* compile templates. However, you may pass an optional compiler object
* to compile template source to include-able PHP code.  It is E_STRICT
* compliant for PHP5.
* 
* Please see the documentation at {@link http://phpsavant.com/}, and be
* sure to donate! :-)
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id$
* 
*/


/**
* Always have these classes available.
*/
include_once dirname(__FILE__) . '/Savant3/Filter.php';
include_once dirname(__FILE__) . '/Savant3/Plugin.php';


/**
* 
* Provides an object-oriented template system for PHP5.
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @package Savant3
* 
* @version 3.0.0dev4
* 
* @todo Unit test 'form' (in progress)
* 
* @todo Unit test for plugin extensions
* 
* @todo Write code analyzer for self :-(
* 
*/

class Savant3 {
	
	
	/**
	* 
	* Array of configuration parameters.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $__config = array(
		'template_path' => array(),
		'resource_path' => array(),
		'error_text'    => "\n\ntemplate error, examine fetch() result\n\n",
		'exceptions'    => false,
		'compiler'      => null,
		'filters'       => array(),
		'plugins'       => array(),
		'template'      => null,
		'plugin_conf'   => array(),
		'extract'       => false,
		'fetch'         => null
	);
	
	
	// -----------------------------------------------------------------
	//
	// Constructor and magic methods
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An associative array of configuration keys for
	* the Savant3 object.  Any, or none, of the keys may be set.
	* 
	* @return object An instance of Savant3.
	* 
	*/
	
	public function __construct($config = null)
	{
		// force the config to an array
		settype($config, 'array');
		
		// set the default template search path
		if (isset($config['template_path'])) {
			// user-defined dirs
			$this->setPath('template', $config['template_path']);
		} else {
			// no directories set, use the
			// default directory only
			$this->setPath('template', null);
		}
		
		// set the default resource search path
		if (isset($config['resource_path'])) {
			// user-defined dirs
			$this->setPath('resource', $config['resource_path']);
		} else {
			// no directories set, use the
			// default directory only
			$this->setPath('resource', null);
		}
		
		// set the error reporting text
		if (isset($config['error_text'])) {
			$this->setErrorText($config['error_text']);
		}
		
		// set the extraction flag
		if (isset($config['extract'])) {
			$this->setExtract($config['extract']);
		}
		
		// set the exceptions flag
		if (isset($config['exceptions'])) {
			$this->setExceptions($config['exceptions']);
		}
		
		// set the template to use for output
		if (isset($config['template'])) {
			$this->setTemplate($config['template']);
		}
		
		// set the default plugin configs
		if (isset($config['plugin_conf']) && is_array($config['plugin_conf'])) {
			foreach ($config['plugin_conf'] as $name => $opts) {
				$this->setPluginConf($name, $opts);
			}
		}
		
		// set the default filter callbacks
		if (isset($config['filters'])) {
			$this->addFilters($config['filters']);
		}
	}
	
	
	/**
	*
	* Executes a plugin method with arbitrary parameters.
	* 
	* @access public
	* 
	* @param string $method The plugin method name.
	*
	* @param array $params The parameters passed to the method.
	*
	* @return mixed The plugin output, or a Savant3_Error with an
	* ERR_PLUGIN code if it can't find the plugin.
	* 
	*/
	
	public function __call($method, $params)
	{
		// shorthand reference
		$plugins =& $this->__config['plugins'];
		
		// is the plugin method object already instantiated?
		if (! array_key_exists($method, $plugins)) {
			
			// not already instantiated, so load it up.
			// set up the class name.
			$class = "Savant3_Plugin_$method";
			
			// has the class been loaded?
			if (! class_exists($class)) {
			
				// class is not loaded, set up the file name.
				$file = "$class.php";
				
				// make sure the class file is available from the resource path.
				$result = $this->findFile('resource', $file);
				if (! $result) {
					// not available, this is an error
					return $this->error(
						'ERR_PLUGIN',
						array('method' => $method)
					);
				} else {
					// available, load the class file
					include_once $result;
				}
			}
			
			// get the default configuration for the plugin.
			$plugin_conf =& $this->__config['plugin_conf'];
			if (! empty($plugin_conf[$method])) {
				$opts = $plugin_conf[$method];
			} else {
				$opts = array();
			}
			
			// add the Savant reference
			$opts['Savant'] = $this;
			
			// instantiate the plugin with its options.
			$plugins[$method] = new $class($opts);
		}
		
		// call the plugin method ...
		$result = call_user_func_array(
			array($plugins[$method], $method),
			$params
		);
		
		// ... and return its results.
		return $result;
	}
	
	
	/**
	* 
	* Magic method to echo this object as template output.
	* 
	* Note that if there is an error, this will output a simple
	* error text string and will not return an error object.  Use
	* fetch() to get an error object when errors occur.
	* 
	* @access public
	* 
	* @param string $tpl The template source to use.
	* 
	* @return string The template output.
	* 
	*/
	
	public function __toString($tpl = null)
	{
		$output = $this->fetch($tpl);
		if ($this->isError($output)) {
			$text = $this->__config['error_text'];
			return $text;
		} else {
			return $output;
		}
	}
	
	
	/**
	* 
	* Reports the API version for this class.
	* 
	* @access public
	* 
	* @return string A PHP-standard version number.
	* 
	*/
	
	public function apiVersion()
	{
		return '@package_version@';
	}
	
	
	// -----------------------------------------------------------------
	//
	// Public configuration management (getters and setters).
	// 
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Returns a copy of the Savant3 configuration parameters.
	*
	* @access public
	* 
	* @param string $key The specific configuration key to return.  If null,
	* returns the entire configuration array.
	* 
	* @return mixed A copy of the $this->__config array.
	* 
	*/
	
	public function getConfig($key = null)
	{
		if (is_null($key)) {
			// no key requested, return the entire config array
			return $this->__config;
		} elseif (empty($this->__config[$key])) {
			// no such key
			return null;
		} else {
			// return the requested key
			return $this->__config[$key];
		}
	}
	
	
	/**
	* 
	* Sets a custom compiler/pre-processor callback for template sources.
	* 
	* By default, Savant3 does not use a compiler; use this to set your
	* own custom compiler (pre-processor) for template sources.
	* 
	* @access public
	* 
	* @param mixed $compiler A compiler callback value suitable for the
	* first parameter of call_user_func().  Set to null/false/empty to
	* use PHP itself as the template markup (i.e., no compiling).
	* 
	* @return void
	* 
	*/
	
	public function setCompiler($compiler)
	{
		$this->__config['compiler'] = $compiler;
	}
	
	
	/**
	* 
	* Sets the custom error text for __toString().
	* 
	* @access public
	* 
	* @param string $text The error text when a template is echoed.
	* 
	* @return void
	* 
	*/
	
	public function setErrorText($text)
	{
		$this->__config['error_text'] = $text;
	}
	
	
	/**
	* 
	* Sets whether or not exceptions will be thrown.
	* 
	* @access public
	* 
	* @param bool $flag True to turn on exception throwing, false
	* to turn it off.
	* 
	* @return void
	* 
	*/
	
	public function setExceptions($flag)
	{
		$this->__config['exceptions'] = (bool) $flag;
	}
	
	
	/**
	* 
	* Sets whether or not variables will be extracted.
	* 
	* @access public
	* 
	* @param bool $flag True to turn on variable extraction, false
	* to turn it off.
	* 
	* @return void
	* 
	*/
	
	public function setExtract($flag)
	{
		$this->__config['extract'] = (bool) $flag;
	}
	
	
	/**
	*
	* Sets config array for a plugin.
	* 
	* @access public
	* 
	* @param string $plugin The plugin to configure.
	* 
	* @param array $config The configuration array for the plugin.
	* 
	* @return void
	*
	*/
	
	public function setPluginConf($plugin, $config = null)
	{
		$this->__config['plugin_conf'][$plugin] = $config;
	}
	
	
	/**
	*
	* Sets the template name to use.
	*
	* @access public
	*
	* @param string $template The template name.
	*
	* @return void
	*
	*/
	
	public function setTemplate($template)
	{
		$this->__config['template'] = $template;
	}
	
	
	// -----------------------------------------------------------------
	//
	// File management
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Sets an entire array of search paths for templates or resources.
	*
	* @access public
	*
	* @param string $type The type of path to set, typically 'template'
	* or 'resource'.
	* 
	* @param string|array $new The new set of search paths.  If null or
	* false, resets to the current directory only.
	*
	* @return void
	*
	*/
	
	public function setPath($type, $new)
	{
		// clear out the prior search dirs
		$this->__config[$type . '_path'] = array();
		
		// convert from string to path
		if (is_string($new) && ! strpos('://', $new)) {
			// the search config is a string, and it's not a stream
			// identifier (the "://" piece), add it as a path string.
			$new = explode(PATH_SEPARATOR, $new);
		} else {
			// force to array
			settype($new, 'array');
		}
		
		// always add the fallback directories as last resort
		switch (strtolower($type)) {
		case 'template':
			// the current directory
			$this->addPath($type, '.');
			break;
		case 'resource':
			// the Savant3 distribution resources
			$this->addPath($type, dirname(__FILE__) . '/Savant3/resources/');
			break;
		}
		
		// actually add the user-specified directories
		foreach ($new as $dir) {
			$this->addPath($type, $dir);
		}
	}
	
	
	/**
	*
	* Adds to the search path for templates and resources.
	*
	* @access public
	*
	* @param string|array $path The directory or stream to search.
	*
	* @return void
	*
	*/
	
	public function addPath($type, $path)
	{
		// make sure $path is an array
		settype($path, 'array');
		
		// loop through the path directories
		foreach ($path as $dir) {
		
			// no surrounding spaces allowed!
			$dir = trim($dir);
			
			// add trailing separators as needed
			if (strpos($dir, '://') && substr($dir, -1) != '/') {
				// stream
				$dir .= '/';
			} elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}
			
			// add to the top of the search dirs
			array_unshift(
				$this->__config[$type . '_path'],
				$dir
			);
		}
	}
	
	
	/**
	* 
	* Searches the directory paths for a given file.
	* 
	* @param array $type The type of path to search (template or resource).
	* 
	* @param string $file The file name to look for.
	* 
	* @return string|bool The full path and file name for the target file,
	* or boolean false if the file is not found in any of the paths.
	*
	*/
	
	protected function findFile($type, $file)
	{
		// get the set of paths
		$set = $this->__config[$type . '_path'];
		
		// start looping through the path set
		foreach ($set as $path) {
			
			// get the path to the file
			$fullname = $path . $file;
			
			// is the path based on a stream?
			if (strpos('://', $path) === false) {
				// not a stream, so do a realpath() to avoid
				// directory traversal attempts on the local file
				// system. Suggested by Ian Eure, initially
				// rejected, but then adopted when the secure
				// compiler was added.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}
			
			// the substr() check added by Ian Eure to make sure
			// that the realpath() results in a directory registered
			// with Savant so that non-registered directores are not
			// accessible via directory traversal attempts.
			if (file_exists($fullname) && is_readable($fullname) &&
				substr($fullname, 0, strlen($path)) == $path) {
				return $fullname;
			}
		}
		
		// could not find the file in the set of paths
		return false;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Variable and reference assignment
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Sets variables for the template (by copy).
	* 
	* This method is overloaded; you can assign all the properties of
	* an object, an associative array, or a single value by name.
	* 
	* <code>
	* $Savant3 = new Savant3();
	* 
	* // assign by object
	* $obj = new stdClass;
	* $obj->var1 = 'something';
	* $obj->var2 = 'else';
	* $Savant3->assign($obj);
	* 
	* // assign by associative array
	* $ary = array('var1' => 'something', 'var2' => 'else');
	* $Savant3->assign($obj);
	* 
	* // assign by name and value
	* $Savant3->assign('var1', 'something');
	* $Savant3->assign('var2', 'else');
	* 
	* // assign directly
	* $Savant3->var1 = 'something';
	* $Savant3->var2 = 'else';
	* </code>
	* 
	* @access public
	* 
	* @return bool True on success, false on failure.
	* 
	*/
	
	public function assign()
	{
		// get the arguments; there may be 1 or 2.
		$arg0 = @func_get_arg(0);
		$arg1 = @func_get_arg(1);
		
		// assign from object
		if (is_object($arg0)) {
			// assign public properties
			foreach (get_object_vars($arg0) as $key => $val) {
				$this->$key = $val;
			}
			return true;
		}
		
		// assign from associative array
		if (is_array($arg0)) {
			foreach ($arg0 as $key => $val) {
				$this->$key = $val;
			}
			return true;
		}
		
		// assign by name and value (can't assign to __config).
		if (is_string($arg0) && func_num_args() > 1 && $arg0 != '__config') {
			$this->$arg0 = $arg1;
			return true;
		}
		
		// $arg0 was not object, array, or string.
		return false;
	}
	
	
	/**
	* 
	* Sets variables for the template (by reference).
	* 
	* <code>
	* $Savant3 = new Savant3();
	* 
	* // assign by name and value
	* $Savant3->assignRef('var1', $ref);
	* 
	* // assign directly
	* $Savant3->ref =& $var1;
	* </code>
	* 
	* @access public
	* 
	* @return bool True on success, false on failure.
	* 
	*/
	
	public function assignRef($key, &$val)
	{
		if ($key != '__config') {
			$this->$key =& $val;
			return true;
		} else {
			return false;
		}
	}
	
	
	// -----------------------------------------------------------------
	//
	// Template processing
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Displays a template directly (equivalent to echo $tpl).
	* 
	* @access public
	* 
	* @param string $tpl The template source to compile and display.
	* 
	* @return void
	* 
	*/
	
	public function display($tpl = null)
	{
		echo $this->__toString($tpl);
	}
	
	
	/**
	* 
	* Compiles, executes, and filters a template source.
	* 
	* @access public
	* 
	* @param string $tpl The template to process; if null, uses the
	* default template set with setTemplate().
	* 
	* @return mixed The template output string, or a Savant3_Error.
	* 
	*/
	
	public function fetch($tpl = null)
	{
		// make sure we have a template source to work with
		if (is_null($tpl)) {
			$tpl = $this->__config['template'];
		}
		
		// get a path to the compiled template script
		$result = $this->template($tpl);
		
		// did we get a path?
		if (! $result || $this->isError($result)) {
		
			// no. return the error result.
			return $result;
			
		} else {
		
			// yes.  execute the template script.  move the script-path
			// out of the local scope, then clean up the local scope to
			// avoid variable name conflicts.
			$this->__config['fetch'] = $result;
			unset($result);
			unset($tpl);
			
			// are we doing extraction?
			if ($this->__config['extract']) {
				// pull variables into the local scope.
				extract(get_object_vars($this), EXTR_REFS);
			}
			
			// buffer output so we can return it instead of displaying.
			ob_start();
			
			// are we using filters?
			if ($this->__config['filters']) {
				// use a second buffer to apply filters. we used to set
				// the ob_start() filter callback, but that would
				// silence errors in the filters. Hendy Irawan provided
				// the next three lines as a "verbose" fix.
				ob_start();
				include $this->__config['fetch'];
				echo $this->applyFilters(ob_get_clean());
			} else {
				// no filters being used.
				include $this->__config['fetch'];
			}
			
			// reset the fetch script value, get the buffer, and return.
			$this->__config['fetch'] = null;
			return ob_get_clean();
		}
	}
	
	
	/**
	*
	* Compiles a template and returns path to compiled script.
	* 
	* By default, Savant does not compile templates, it uses PHP as the
	* markup language, so the "compiled" template is the same as the source
	* template.
	* 
	* Usage:
	* 
	* <code>
	* include $this->template($tpl);
	* </code>
	* 
	* @access protected
	*
	* @param string $tpl The template source name to look for.
	* 
	* @return string The full path to the compiled template script.
	* 
	* @throws object An error object with a 'ERR_TEMPLATE' code.
	* 
	*/
	
	protected function template($tpl = null)
	{
		// set to default template if none specified.
		if (is_null($tpl)) {
			$tpl = $this->__config['template'];
		}
		
		// find the template source.
		$file = $this->findFile('template', $tpl);
		if (! $file) {
			return $this->error(
				'ERR_TEMPLATE',
				array('template' => $tpl)
			);
		}
		
		// are we compiling source into a script?
		if ($this->__config['compiler']) {
			// compile the template source and get the path to the
			// compiled script (will be returned instead of the
			// source path)
			$result = call_user_func(
				array($this->__config['compiler'], 'compile'),
				$file
			);
		} else {
			// no compiling requested, use the source path
			$result = $file;
		}
		
		// is there a script from the compiler?
		if (! $result || $this->isError($result)) {
			// return an error, along with any error info
			// generated by the compiler.
			return $this->error(
				'ERR_COMPILER',
				array(
					'template' => $tpl,
					'compiler' => $result
				)
			);
		} else {
			// no errors, the result is a path to a script
			return $result;
		}
	}
	
	
	// -----------------------------------------------------------------
	//
	// Filter management and processing
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Resets the filter stack to the provided list of callbacks.
	* 
	* @access protected
	* 
	* @param array An array of filter callbacks.
	* 
	* @return void
	* 
	*/
	
	public function setFilters()
	{
		$this->__config['filters'] = (array) @func_get_args();
	}
	
	
	/**
	* 
	* Adds filter callbacks to the stack of filters.
	* 
	* @access protected
	* 
	* @param array An array of filter callbacks.
	* 
	* @return void
	* 
	*/
	
	public function addFilters()
	{
		// add the new filters to the static config variable
		// via the reference
		foreach ((array) @func_get_args() as $callback) {
			$this->__config['filters'][] = $callback;
		}
	}
	
	
	/**
	* 
	* Runs all filter callbacks on buffered output.
	* 
	* @access protected
	* 
	* @param string The template output.
	* 
	* @return void
	* 
	*/
	
	protected function applyFilters($buffer)
	{
		foreach ($this->__config['filters'] as $callback) {
		
			// if the callback is a static Savant3_Filter method,
			// and not already loaded, try to auto-load it.
			if (is_array($callback) &&
				is_string($callback[0]) &&
				substr($callback[0], 0, 15) == 'Savant3_Filter_' &&
				! class_exists($callback[0])) {
				
				// load the Savant3_Filter_*.php resource
				$file = $callback[0] . '.php';
				$result = $this->findFile('resource', $file);
				if ($result) {
					include_once $result;
				}
			}
			
			// can't pass a third $this param, it chokes the OB system.
			$buffer = call_user_func($callback, $buffer);
		}
		
		return $buffer;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Error handling
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Returns an error object or throws an exception.
	* 
	* @access public
	* 
	* @param string $code A Savant3 'ERR_*' string.
	* 
	* @param array $info An array of error-specific information.
	* 
	* @param int $level The error severity level, default is
	* E_USER_ERROR (the most severe possible).
	* 
	* @param bool $trace Whether or not to include a backtrace, default
	* true.
	* 
	* @return object An error object of the type specified by
	* $this->_error.
	* 
	*/
	
	public function error($code, $info = array(), $level = E_USER_ERROR,
		$trace = true)
	{
		// are we throwing exceptions?
		if ($this->__config['exceptions']) {
			if (! class_exists('Savant3_Exception')) {
				include_once dirname(__FILE__) . '/Savant3/Exception.php';
			}
			throw new Savant3_Exception($code);
		}
		
		
		// the error config array
		$config = array(
			'code'  => $code,
			'info'  => (array) $info,
			'level' => $level,
			'trace' => $trace
		);
		
		// make sure the Savant3 error class is available
		if (! class_exists('Savant3_Error')) {
			include_once dirname(__FILE__) . '/Savant3/Error.php';
		}
		
		// return it
		$err = new Savant3_Error($config);
		return $err;
	}
	
	
	/**
	*
	* Tests if an object is of the Savant3_Error class.
	* 
	* @access public
	* 
	* @param object &$obj The object to be tested.
	* 
	* @return boolean True if $obj is an error object of the type
	* Savant3_Error, or is a subclass that Savant3_Error. False if not.
	*
	*/
	
	public function isError($obj)
	{
		// is the object a Savant3_Error?
		if (! is_object($obj)) {
			return false;
		} else {
			// make sure the Savant3 error class is available
			if (! class_exists('Savant3_Error')) {
				include_once dirname(__FILE__) . '/Savant3/Error.php';
			}
			// now check for parentage
			$is = $obj instanceof Savant3_Error;
			$sub = is_subclass_of($obj, 'Savant3_Error');
			return ($is || $sub);
		}
	}
}
?>