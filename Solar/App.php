<?php

class Solar_App extends Solar_Base {
	
	public $config = array(
		'models'      => null,
		'views'       => null,
		'controllers' => null,
		'helpers'     => null,
		'locale'      => null,
		'get_var'     => 'action',
	);
	
	protected $map = array(
		'models'      => array(),
		'views'       => array(),
		'controllers' => array(),
		'helpers'     => array(),
	);
	
	protected $default_controller = null;
	
	public function __construct($config = null)
	{
		// get the application class name, minus the 'Solar_App_'
		// prefix.
		$app = substr(get_class($this), 9);
		
		// get the baseline app directory
		$dir = dirname(__FILE__) . "/$app";
		
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
		
		// done!
	}
	
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
	
	public function run()
	{
		// find the requested action
		$action = Solar::get(
			$this->config['get_var'],
			$this->config['default']
		);
		
		// is there a controller mapped for the requested action?
		if (in_array($action, $this->map['controllers'])) {
			return $this->controller($action);
		} else {
			// unknown action, revert to default controller action
			return $this->controller($this->default_controller);
		}
	}
	
	protected function controller()
	{
		return include $this->config['controllers'] . func_get_arg(0) . '.php';
	}
	
	protected function view()
	{
		return include $this->config['views'] . func_get_arg(0) . '.php';
	}
	
	protected function helper()
	{
		return include $this->config['helpers'] . func_get_arg(0) . '.php';
	}
	
	protected function model()
	{
		return include $this->config['models'] . func_get_arg(0) . '.php'
	}
}

?>