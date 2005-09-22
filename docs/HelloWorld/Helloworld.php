<?php
Solar::loadClass('Solar_App');

class Helloworld extends Solar_App {

	function setup()
	{
		$this->dir['base'] = dirname(__FILE__);
		$this->action['default'] = 'default';
	}

}
?>