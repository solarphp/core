<?php

/**
* 
* Provides an object-oriented template system for PHP5.
* 
* Savant3 helps you separate model logic from view logic using PHP as
* the template language. By default, Savant3 does not compile templates.
* However, you may pass an optional compiler object to compile template
* source to include-able PHP code.  It is E_STRICT compliant for PHP5.
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
* @version $Id: Savant3.php,v 1.14 2005/03/07 14:09:05 pmjones Exp $
* 
*/


/**
* Always have these classes available.
*/
include_once dirname(__FILE__) . '/Savant3/Error.php';
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
* @version 3.0.0dev2
* 
* @todo Unit test 'form' (in progress)
* 
* @todo Unit test for plugin extensions
* 
* @todo Write code analyzer for self :-(
* 
*/

class Savant3 {
	
	
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
	* @param array $conf An associative array of configuration keys for
	* the Savant3 object.  Any, or none, of the keys may be set.
	* 
	* @return object An instance of Savant3.
	* 
	*/
	
	public function __construct($conf = null)
	{
		// set up the basic config
		$this->conf();
		
		// force the conf to an array
		settype($conf, 'array');
		
		// set the default template search path
		if (isset($conf['template_path'])) {
			// user-defined dirs
			$this->setPath('template', $conf['template_path']);
		} else {
			// no directories set, use the
			// default directory only
			$this->setPath('template', null);
		}
		
		// set the default resource search path
		if (isset($conf['resource_path'])) {
			// user-defined dirs
			$this->setPath('resource', $conf['resource_path']);
		} else {
			// no directories set, use the
			// default directory only
			$this->setPath('resource', null);
		}
		
		// set the error reporting type
		if (isset($conf['error_type'])) {
			$this->setErrorType($conf['error_type']);
		}
		
		// set the error reporting text
		if (isset($conf['error_text'])) {
			$this->setErrorText($conf['error_text']);
		}
		
		// set the error reporting text
		if (isset($conf['extract'])) {
			$this->setExtract($conf['extract']);
		}
		
		// set the template to use for output
		if (isset($conf['template'])) {
			$this->setTemplate($conf['template']);
		}
		
		// set the default plugin configs
		if (isset($conf['plugin_conf']) && is_array($conf['plugin_conf'])) {
			foreach ($conf['plugin_conf'] as $name => $opts) {
				$this->setPluginConf($name, $opts);
			}
		}
		
		// set the default filter callbacks
		if (isset($conf['filters']) && is_array($conf['filters'])) {
			foreach ($conf['filters'] as $callback) {
				$this->addFilter($callback);
			}
		}
		
		// set the i18n error messages
		if (isset($conf['i18n']) && is_array($conf['i18n'])) {
			$i18n =& $this->conf('i18n');
			$i18n = $conf['i18n'];
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
		// keep the plugin instances inside this method,
		// away from the rest of the object.
		static $plugin;
		if (! isset($plugin)) {
			$plugin = array();
		}
		
		// is the plugin method object already instantiated?
		if (! array_key_exists($method, $plugin)) {
			
			// not already instantiated, load it up.
			// set up the class and file names.
			$class = "Savant3_Plugin_$method";
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
			
			// get the default configuration for the plugin.
			$plugin_conf = $this->conf('plugin_conf');
			if (! empty($plugin_conf[$method])) {
				$opts = $plugin_conf[$method];
			} else {
				$opts = null;
			}
			
			// instantiate the plugin with its options.
			$plugin[$method] = new $class($opts, $this);
		}
		
		// call the plugin method ...
		$result = call_user_func_array(
			array($plugin[$method], $method),
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
			$text = $this->conf('error_text');
			return $text;
		} else {
			return $output;
		}
	}
	
	
	/**
	* 
	* Reports the API version for this class.
	* 
	* If you don't override this method, your classes will use the same
	* API version string as the Solar package itself.
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
	// Protected configuration management.
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Returns a reference to the Savant3 configuration parameters.
	* 
	* @access protected
	* 
	* @param string $key The specific configuration key to return.  If null,
	* returns the entire configuration array.
	* 
	* @return mixed A reference to the internal static $conf array.
	* 
	*/
	
	protected function &conf($key = null)
	{
		// share the $conf variable across method calls within this instance
		static $conf;
		
		// set the default values
		if (! isset($conf)) {
			$conf = array(
				'i18n' => array(
					'ERR_UNKNOWN'    => 'unknown error',
					'ERR_COMPILER'   => 'compiler callback returned an error',
					'ERR_PLUGIN'     => 'plugin class file not found',
					'ERR_TEMPLATE'   => 'template source file not found',
				),
				'template_path' => array(),
				'resource_path' => array(),
				'error_type'    => null,
				'error_text'    => "\n\ntemplate error, examine fetch() result\n\n",
				'compiler'      => null,
				'filters'       => array(),
				'template'      => null,
				'plugin_conf'   => array(),
				'extract'       => false,
				'fetch'         => null
			);
		}
		
		// find and return the requested element
		if (is_null($key)) {
			// no key requested, return the entire conf array
			return $conf;
		} else {
			// return the requested key
			return $conf[$key];
		}
	}
	
	
	/**
	*
	* Sets a configuration parameters
	*
	* @access protected
	* 
	* @param string $key The specific configuration key to set.
	* 
	* @param mixed $val The value to set it to.
	* 
	* @return void
	* 
	*/
	
	protected function setConf($key, $val)
	{
		$tmp =& $this->conf();
		$tmp[$key] = $val;
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
	* @return mixed A copy of the $conf array.
	* 
	*/
	
	public function getConf($key = null)
	{
		return $this->conf($key);
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
		$this->setConf('compiler', $compiler);
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
		$this->setConf('error_text', $text);
	}
	
	
	/**
	* 
	* Sets the custom error type for Savant3 errors.
	* 
	* @access public
	* 
	* @param string $type The error type, e.g. 'exception' or 'pear'. If
	* null or false, resets the error class to 'Savant3_Error'.
	* 
	* @return void
	* 
	*/
	
	public function setErrorType($type)
	{
		$this->setConf('error_type', $type);
	}
	
	
	/**
	* 
	* Sets whterh or not variables will be extracted.
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
		$this->setConf('extract', (bool) $flag);
	}
	
	
	/**
	*
	* Sets config array for a plugin.
	*
	*/
	
	public function setPluginConf($plugin, $conf = null)
	{
		$tmp =& $this->conf('plugin_conf');
		$tmp[$plugin] = $conf;
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
		$this->setConf('template', $template);
	}
	
	
	// -----------------------------------------------------------------
	//
	// File management
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Sets an entire array of search paths.
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
		// reference the current path config
		$path =& $this->conf($type . '_path');
		
		// clear out the prior search dirs
		$path = array();
		
		// convert from string to path
		if (is_string($new) && ! strpos('://', $new)) {
			// the search config is a string, and it's not a stream
			// identifier (the "://" piece), add it as a path
			// string.
			$new = explode(PATH_SEPARATOR, $new);
		} else {
			// force to array
			settype($new, 'array');
		}
		
		// always add the fallback directories as last resort
		switch (strtolower($type)) {
		case 'template':
			$this->addPath($type, '.');
			break;
		case 'resource':
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
	* @param string $dir The directory or stream to search.
	*
	* @return void
	*
	*/
	
	public function addPath($type, $dir)
	{
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
		$path =& $this->conf($type . '_path');
		array_unshift($path, $dir);
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
		$set = $this->conf($type . '_path');
		
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
		
		// assign by name and value
		if (is_string($arg0) && func_num_args() > 1) {
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
		$this->$key =& $val;
		return true;
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
			$tpl = $this->conf('template');
		}
		
		// get a path to the compiled template script
		$result = $this->compile($tpl);
		
		// did we get a path?
		if (! $result || $this->isError($result)) {
		
			// no. return the error result.
			return $result;
			
		} else {
		
			// yes.  execute the template script.  move the script-path
			// out of the local scope, then clean up the local scope to
			// avoid variable name conflicts.
			$this->setConf('fetch', $result);
			unset($result);
			unset($tpl);
			
			// are we doing extraction?
			if ($this->conf('extract')) {
				// pull variables into the local scope.
				extract(get_object_vars($this, EXTR_REFS));
			}
			
			// buffer output so we can return it instead of displaying.
			ob_start();
			
			// are we using filters?
			if ($this->conf('filters')) {
				// use internal buffer to apply filter callbacks.
				ob_start(array($this, 'applyFilters'));
				include $this->conf('fetch');
				ob_end_flush();
			} else {
				// no filters being used.
				include $this->conf('fetch');
			}
			
			// reset the fetch script value, get the buffer, and return.
			$this->setConf('fetch', null);
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
	* @access protected
	*
	* @param string $tpl The template source name to look for.
	* 
	* @return string The full path to the compiled template script.
	* 
	* @throws object An error object with a 'ERR_TEMPLATE' code.
	* 
	*/
	
	protected function compile($tpl = null)
	{
		// set to default template if none specified.
		if (is_null($tpl)) {
			$tpl = $this->conf('template');
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
		$compiler = $this->conf('compiler');
		if ($compiler) {
			// compile the template source and get the path to the
			// compiled script (will be returned instead of the
			// source path)
			$result = call_user_func($compiler, $file, $this);
		} else {
			// no compiling requested, return the source path
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
	
	
	/**
	* 
	* Gets the path to the compiled script for a given template.
	* 
	* Usage:
	* 
	* <code>
	* include $this->template($tpl);
	* </code>
	* 
	* @access protected
	* 
	* @param string $tpl The template source.
	* 
	* @return void
	* 
	*/
	
	protected function template($tpl)
	{
		return $this->compile($tpl);
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
	* This method will take the
	* 
	* @access protected
	* 
	* @param string The template file to include.
	* 
	* @return void
	* 
	*/
	
	public function setFilters()
	{
		$filters =& $this->conf('filters');
		$filters = (array) @func_get_args();
	}
	
	
	/**
	* 
	* Adds filter callbacks to the stack of filters.
	* 
	* @access protected
	* 
	* @param string The template file to include.
	* 
	* @return void
	* 
	*/
	
	public function addFilters()
	{
		$filters =& $this->conf('filters');
		foreach ((array) @func_get_args() as $callback) {
			$filters[] = $callback;
		}
	}
	
	
	/**
	* 
	* Runs all filter callbacks on buffered output.
	* 
	* @access protected
	* 
	* @param string The template file to include.
	* 
	* @return void
	* 
	*/
	
	protected function applyFilters($buffer)
	{
		$filters = $this->conf('filters');
		
		foreach ($filters as $callback) {
		
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
	* Returns an error object.
	* 
	* @access public
	* 
	* @param string $code A Savant3 'ERR_*' string.
	* 
	* @param array $info An array of error-specific information.
	* 
	* @return object An error object of the type specified by
	* $this->_error.
	* 
	*/
	
	public function &error($code, $info = array(), $trace = true)
	{
		// the error config array
		$conf = array(
			'code'  => $code,
			'text'  => 'Savant3: ',
			'info'  => (array) $info,
			'trace' => $trace
		);
		
		// get i18n error messages
		$messages = $this->conf('i18n');
		
		// set the error message
		if (! empty($messages[$code])) {
			$conf['text'] .= $messages[$code];
		} else {
			$conf['text'] .= '???';
		}
		
		// the default error class
		$class = 'Savant3_Error';
		
		// is a custom type defined?
		$errtype = $this->conf('error_type');
		
		if ($errtype) {
			
			// set up the error class name
			$class = 'Savant3_Error_' . $errtype;
			
			// is it loaded?
			while (! class_exists($class)) {
				
				// set up the error class file name
				$file = $class . '.php';
				
				// find the error class
				$result = $this->findFile('resource', $file);
				if (! $result) {
					// could not find the custom error class, revert to
					// Savant_Error base class.
					$class = 'Savant3_Error';
					$result = dirname(__FILE__) . '/Savant3/Error.php';
				}
				
				// include the error class
				include_once $result;
				
				// did it work?
				if (! class_exists($class)) {
					$class = 'Savant3_Error';
				}
			}

		}
		
		// instantiate and return the error class (by reference, which
		// is why we need the extra variable)
		$err = new $class($conf);
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
	
	public function isError(&$obj)
	{
		if (! is_object($obj)) {
			return false;
		} else {
			$is = $obj instanceof Savant3_Error;
			$sub = is_subclass_of($obj, 'Savant3_Error');
			return ($is || $sub);
		}
	}
}
?>