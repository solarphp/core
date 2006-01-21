--TEST--
base: $this->error() calls
--FILE---
<?php
require_once '../setup.php';
Solar::start();

// reset error callbacks to nothing, turn off traces
Solar::$config['Solar_Error']['push_callback'] = null;
Solar::$config['Solar_Error']['pop_callback']  = null;
Solar::$config['Solar_Error']['trace']         = false;

// example class for testing
class Example extends Solar_Base {
	
	public function __construct($config = null)
	{
		$this->_config['locale'] = dirname(__FILE__) . '/locale/';
		parent::__construct();
	}
	
	public function something()
	{
		return $this->_error('ERR_EXAMPLE');
	}
	
	public function another()
	{
		return $this->_error(
			'ERR_EXAMPLE',         // code
			array('baz' => 'dib'), // info
			E_USER_ERROR,          // level
			false                  // trace
		);
	}
}

// test the Solar_Base::error() method (simple)
$example = new Example();
$err = $example->something();
Solar::dump($err);

// test the Solar_Base::error() method (extended)
$err = $example->another();
Solar::dump($err);

Solar::stop();
?>
--EXPECT--
object(Solar_Error)#9 (2) {
  ["_config:protected"] => array(5) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
    ["locale"] => string(19) "Solar/Error/Locale/"
  }
  ["_stack:protected"] => array(1) {
    [0] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(25) "This is an error message."
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
  }
}
object(Solar_Error)#10 (2) {
  ["_config:protected"] => array(5) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
    ["locale"] => string(19) "Solar/Error/Locale/"
  }
  ["_stack:protected"] => array(1) {
    [0] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(25) "This is an error message."
      ["info"] => array(1) {
        ["baz"] => string(3) "dib"
      }
      ["level"] => int(256)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
  }
}