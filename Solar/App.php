<?php

class Solar_App extends Solar_Base {
	
	public $config = array(
		'locale'      => null,
		'models'      => null,
		'views'       => null,
		'controllers' => null,
		'helpers'     => null,
		'get_var'     => 'action',
		'default'     => null,
	);
	
	protected $map = array(
		'models'      => array(),
		'views'       => array(),
		'controllers' => array(),
		'helpers'     => array(),
	);
	
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
		$this->config['helpers'] = Solar::fixdir("$dir/Helpers/");
		$this->config['locale'] = Solar::fixdir("$dir/helpers/locale/");
		
		// now do the "real" construction
		parent::__construct($config);
		
		// build the map of controllers, models, views, and helpers
		$this->automap('models');
		$this->automap('views');
		$this->automap('controllers');
		$this->automap('helpers');
		
		// done!
	}
	
	public function run()
	{
		// find the requested action
		$action = Solar::get(
			$this->config['get_var'],
			$this->config['default']
		);
		
		// is there a controller script for the requested action?
		$keys = array_keys($this->map['controllers']);
		if (in_array($action, $keys)) {
			// yes, there's a known script
			$file = $this->map['controllers'][$action];
		} else {
			// unknown action, revert to default
			$file = $this->map['controllers'][$this->config['default']]);
		}
		
		// perform the requested action
		return $this->controller($file);
	}
	
	protected function controller()
	{
		return include $this->config['controllers'] . func_get_arg(0);
	}
	
	protected function view()
	{
		return include $this->config['views'] . func_get_arg(0);
	}
	
	protected function helper()
	{
		return include $this->config['helpers'] . func_get_arg(0);
	}
	
	protected function model()
	{
		return include $this->config['models'] . func_get_arg(0);
	}
	
	protected function automap($type)
	{
		$files = scandir($this->config[$type]);
		foreach ($files as $file) {
			// look for *.php files (no dotfiles)
			if (substr($file, 0, 1) != '.' && substr($file, -4) == '.php') {
				$name = substr($file, 0, -4);
				$this->map[$type][$name] = $file;
			}
		}
	}
}

?>