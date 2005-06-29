--TEST--
base: $this->errorPush() calls
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
		$this->config['locale'] = dirname(__FILE__) . '/locale/';
	}
	
	public function something()
	{	
		$error = Solar::object('Solar_Error');
		for ($i = 0; $i < 3; $i++) {
			$this->errorPush($error, 'ERR_EXAMPLE', array('i' => $i));
		}
		return $error;
	}
}

// test the Solar_Base::errorPush() method
$example = new Example();
$err = $example->something();
dump($err);

Solar::stop();
?>
--EXPECT--
object(Solar_Error)#10 (2) {
  ["config:protected"] => array(4) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
  }
  ["stack:protected"] => array(3) {
    [0] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(25) "This is an error message."
      ["info"] => array(1) {
        ["i"] => int(0)
      }
      ["level"] => int(1024)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
    [1] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(25) "This is an error message."
      ["info"] => array(1) {
        ["i"] => int(1)
      }
      ["level"] => int(1024)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
    [2] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(25) "This is an error message."
      ["info"] => array(1) {
        ["i"] => int(2)
      }
      ["level"] => int(1024)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
  }
}