--TEST--
error: Solar::error() calls
--FILE---
<?php
require_once '../setup.php';
Solar::start();

// reset error callbacks to nothing, turn off traces
Solar::$config['Solar_Error']['push_callback'] = null;
Solar::$config['Solar_Error']['pop_callback']  = null;
Solar::$config['Solar_Error']['trace']         = false;

// Solar::error basics
$err = Solar::error(
	'Example',        // class
	'ERR_EXAMPLE'    // code
);

dump($err);

// Solar::error extended
$err = Solar::error(
	'Example',         // class
	'ERR_EXAMPLE',     // code
	'Example error',   // text
	array(             // info
		'foo' => 'bar'
	),
	E_USER_WARNING,    // level
	false              // trace
);

dump($err);

Solar::stop();
?>
--EXPECT--
object(Solar_Error)#9 (2) {
  ["config:protected"] => array(4) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
  }
  ["stack:protected"] => array(1) {
    [0] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(0) ""
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
  }
}
object(Solar_Error)#10 (2) {
  ["config:protected"] => array(4) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
  }
  ["stack:protected"] => array(1) {
    [0] => array(7) {
      ["class"] => string(7) "Example"
      ["code"] => string(11) "ERR_EXAMPLE"
      ["text"] => string(13) "Example error"
      ["info"] => array(1) {
        ["foo"] => string(3) "bar"
      }
      ["level"] => int(512)
      ["class::code"] => string(20) "Example::ERR_EXAMPLE"
      ["trace"] => NULL
    }
  }
}