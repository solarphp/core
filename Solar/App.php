<?php

/**
* 
* Abstract application controller class for Solar.
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
* Abstract application controller class for Solar.
* 
* @category Solar
* 
* @package Solar
* 
*/

abstract class Solar_App extends Solar_Base {
	
	/**
	* 
	* User-defined configuration array.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'models'      => null,
		'views'       => null,
		'controllers' => null,
		'helpers'     => null,
		'locale'      => null,
		'get_var'     => 'action',
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
	* The default controller action name to use.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	protected $default_controller = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		// get the application class name, minus the 'Solar_App_'
		// prefix.
		$app = substr(get_class($this), 10);
		
		// get the baseline app directory
		$dir = dirname(__FILE__) . "/App/$app";
		
		// set up the default directory path properties
		$this->config['models'] = Solar::fixdir("$dir/models/");
		$this->config['views'] = Solar::fixdir("$dir/views/");
		$this->config['controllers'] = Solar::fixdir("$dir/controllers/");
		$this->config['helpers'] = Solar::fixdir("$dir/helpers/");
		$this->config['locale'] = Solar::fixdir("$dir/helpers/locale/");
		
		// now do the "real" construction
		parent::__construct($config);
		
		// build the map of models, controllers, views, and helpers
		$this->automap('models');
		$this->automap('views');
		$this->automap('controllers');
		$this->automap('helpers');
		
		// load the locale strings
		$this->locale('');
	}
	
	
	/**
	* 
	* Builds $this-map for a given type (model, view, etc).
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
		$files = scandir($this->config[$type]);
		foreach ($files as $file) {
			// look for *.php files (no dotfiles)
			if (substr($file, 0, 1) != '.' && substr($file, -4) == '.php') {
				$name = substr($file, 0, -4);
				$this->map[$type][] = $name;
			}
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
			$action = Solar::get(
				$this->config['get_var'],
				$this->default_controller
			);
		}
		
		// is there a controller mapped for the requested action?
		if (in_array($action, $this->map['controllers'])) {
			$file = $this->controller($action);
		} else {
			// unknown action, revert to default controller action
			$file = $this->controller($this->default_controller);
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
	* Returns the file path for a named model.
	* 
	* @access protected
	* 
	* @param string $name The model name.
	* 
	* @return string The path to the named model.
	* 
	*/
	
	protected function model($name)
	{
		return $this->config['models'] . "$name.php";
	}
	
	
	/**
	* 
	* Returns the file path for a named view.
	* 
	* @access protected
	* 
	* @param string $name The view name.
	* 
	* @return string The path to the named view.
	* 
	*/
	
	protected function view($name)
	{
		return $this->config['views'] . "$name.php";
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
		return $this->config['controllers'] . "$name.php";
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
		return $this->config['helpers'] . "$name.php";
	}
}

?>