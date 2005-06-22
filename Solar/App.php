<?php

/**
* 
* Abstract application controller class for Solar.
* 
* @category Solar
* 
* @package Solar_App
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
* Abstract application controller class for Solar.
* 
* @category Solar
* 
* @package Solar_App
* 
*/

abstract class Solar_App extends Solar_Base {
	
	/**
	* 
	* User-defined configuration array.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'locale'     => null,
		'views'      => null,
	);
	
	
	/**
	* 
	* Where the component type directories are located.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	protected $dir = array(
		'base'        => null,
		'models'      => null,
		'views'       => null,
		'controllers' => null,
		'helpers'     => null
	);
	
	
	/**
	* 
	* Mapping array of discovered scripts.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	protected $map = array(
		'models'      => array(),
		'views'       => array(),
		'controllers' => array(),
		'helpers'     => array(),
	);
	
	
	/**
	* 
	* The Solar method to use for finding the action (get, post, pathinfo).
	* The action variable name from the action source.
	* The default action to perform.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $action = array(
		'src'     => 'get',
		'var'     => 'action',
		'default' => null
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		// basic property setup
		$this->setup();
		
		// is the base directory set?
		if (empty($this->dir['base'])) {
			
			// we need a base directory
			$this->dir['base'] = dirname(__FILE__);
			
			// get the class name ...
			$class = get_class($this);
			
			// ... is it a Solar_App class?
			if (substr($class, 0, 10) == 'Solar_App_') {
				// get the application class name, minus the
				// 'Solar_App_' prefix, and set to the standard
				// base directory location.
				$app = substr($class, 10);
				$this->dir['base'] .= "/App/$app";
			}
		}
		
		// the component type directories and maps (used for looping
		// later)
		$types = array('models', 'views', 'controllers', 'helpers');
		
		// set up the default directory path properties if they are not
		// already specified
		$base = $this->dir['base'];
		foreach ($types as $type) {
			if (empty($this->dir[$type])) {
				$this->dir[$type] = Solar::fixdir("$base/$type/");
			}
		}
		
		// set up the default locale path is one is not already
		// specified
		if (empty($this->config['locale'])) {
			$this->config['locale'] = Solar::fixdir(
				$this->dir['helpers'] . 'locale/'
			);
		}
		
		// now do the "real" construction
		parent::__construct($config);
		
		// build the filename map of model, controller, view, and helper
		// scripts
		foreach ($types as $type) {
			$this->automap($type);
		}
		
		// set up a view object (really just Solar_Template, which itself
		// is really just Savant3).
		$this->view = Solar::shared('template');
		
		// add the default path to the application views
		$this->view->addPath('template', $this->dir['views']);
		
		// in the config file, if you have a key called 'views' for
		// this class, those are where the overriding theme files reside.
		if (! empty($this->config['views'])) {
			$this->view->addPath('template', $this->config['views']);
			$this->view->addPath('resource', $this->config['views']);
		}
		
		// load the locale strings
		$this->locale('');
	}
	
	
	/**
	* 
	* Sets up class properties for extended classes.
	* 
	* We have a method for this so you can use functions and other logic
	* for defining properties in extended classes.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	protected function setup()
	{
	}
	
	
	/**
	* 
	* Builds $this->map for a given type (model, view, etc).
	* 
	* @access protected
	* 
	* @param string $type The mapping type to look for.
	* 
	* @return void
	* 
	*/
	
	protected function automap($type)
	{
		if (is_dir($this->dir[$type])) {
			$files = scandir($this->dir[$type]);
			foreach ($files as $file) {
				// look for *.php files (no dotfiles)
				if (substr($file, 0, 1) != '.' && substr($file, -4) == '.php') {
					$name = substr($file, 0, -4);
					$this->map[$type][] = $name;
				}
			}
		} else {
			$this->map[$type] = array();
		}
	}
	
	
	/**
	* 
	* Executes the requested controller action and returns the output.
	* 
	* @access public
	* 
	* @param string $action The controller action to execute.
	* 
	* @return void
	* 
	*/
	
	public function output($action = null)
	{
		if (is_null($action)) {
			// find the requested action
			$action = call_user_func(
				// Solar::get(), Solar::post(), or Solar::pathinfo()
				array('Solar', $this->action['src']),
				// the requested action
				$this->action['var'],
				// the default action
				$this->action['default']
			);
		}
		
		// is there a controller mapped for the requested action?
		if (in_array($action, $this->map['controllers'])) {
			$file = $this->controller($action);
		} else {
			// unknown action, revert to default controller action
			$file = $this->controller($this->action['default']);
		}
		
		// return the output
		return $this->run($file);
	}
	
	
	/**
	* 
	* Includes a file in an isolated scope (but with access to $this).
	* 
	* @access protected
	* 
	* @param string The file to include.
	* 
	* @return mixed The return from the included file.
	* 
	*/
	
	protected function run()
	{
		return include func_get_arg(0);
	}
	
	
	/**
	* 
	* Returns a new model object by name.
	* 
	* @access protected
	* 
	* @param string $name The model name.
	* 
	* @param array $config User-defined configuration for the model.
	* 
	* @return object The request model object.
	* 
	*/
	
	protected function model($name, $config = null)
	{
		if (! class_exists($name)) {
			include_once $this->dir['models'] . $name . '.php';
		}
		
		$obj = new $name($config);
		return $obj;
	}
	
	
	/**
	* 
	* Returns the output generated by a named view.
	* 
	* @access protected
	* 
	* @param string $name The view name.
	* 
	* @return string The output of the named view.
	* 
	*/
	
	protected function view($name)
	{
		$file = $name . '.php';
		return $this->view->fetch($file);
	}
	
	
	/**
	* 
	* Returns the file path for a named controller.
	* 
	* @access protected
	* 
	* @param string $name The controller name.
	* 
	* @return string The path to the named controller.
	* 
	*/
	
	protected function controller($name)
	{
		return $this->dir['controllers'] . $name . '.php';
	}
	
	
	/**
	* 
	* Returns the file path for a named helper.
	* 
	* @access protected
	* 
	* @param string $name The helper name.
	* 
	* @return string The path to the named helper.
	* 
	*/
	
	protected function helper($name)
	{
		return $this->dir['helpers'] . $name . '.php';
	}
}

?>