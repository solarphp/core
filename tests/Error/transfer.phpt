--TEST--
error: Solar_Base::error() calls
--FILE---
<?php
require_once '../setup.php';
Solar::start();

// reset error callbacks to nothing, turn off traces
Solar::$config['Solar_Error']['push_callback'] = null;
Solar::$config['Solar_Error']['pop_callback']  = null;
Solar::$config['Solar_Error']['trace']         = false;

$a = Solar::object('Solar_Error');
for ($i = 0; $i < 5; $i++) {
	$a->push(
		'Test_A',
		'ERR_TEST_A',
		"Message #$i"
	);
}

$b = Solar::object('Solar_Error');
for ($i = 0; $i < 3; $i++) {
	$b->push(
		'Test_B',
		'ERR_TEST_B',
		"Message #$i"
	);
}

$b->push($a);

Solar::dump($b);

Solar::stop();
?>
--EXPECT--
object(Solar_Error)#9 (2) {
  ["config:protected"] => array(5) {
    ["push_callback"] => NULL
    ["pop_callback"] => NULL
    ["trace"] => bool(false)
    ["level"] => int(1024)
    ["locale"] => string(19) "Solar/Error/Locale/"
  }
  ["stack:protected"] => array(8) {
    [0] => array(7) {
      ["class"] => string(6) "Test_A"
      ["code"] => string(10) "ERR_TEST_A"
      ["text"] => string(10) "Message #0"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_A::ERR_TEST_A"
      ["trace"] => NULL
    }
    [1] => array(7) {
      ["class"] => string(6) "Test_A"
      ["code"] => string(10) "ERR_TEST_A"
      ["text"] => string(10) "Message #1"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_A::ERR_TEST_A"
      ["trace"] => NULL
    }
    [2] => array(7) {
      ["class"] => string(6) "Test_A"
      ["code"] => string(10) "ERR_TEST_A"
      ["text"] => string(10) "Message #2"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_A::ERR_TEST_A"
      ["trace"] => NULL
    }
    [3] => array(7) {
      ["class"] => string(6) "Test_A"
      ["code"] => string(10) "ERR_TEST_A"
      ["text"] => string(10) "Message #3"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_A::ERR_TEST_A"
      ["trace"] => NULL
    }
    [4] => array(7) {
      ["class"] => string(6) "Test_A"
      ["code"] => string(10) "ERR_TEST_A"
      ["text"] => string(10) "Message #4"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_A::ERR_TEST_A"
      ["trace"] => NULL
    }
    [5] => array(7) {
      ["class"] => string(6) "Test_B"
      ["code"] => string(10) "ERR_TEST_B"
      ["text"] => string(10) "Message #0"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_B::ERR_TEST_B"
      ["trace"] => NULL
    }
    [6] => array(7) {
      ["class"] => string(6) "Test_B"
      ["code"] => string(10) "ERR_TEST_B"
      ["text"] => string(10) "Message #1"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_B::ERR_TEST_B"
      ["trace"] => NULL
    }
    [7] => array(7) {
      ["class"] => string(6) "Test_B"
      ["code"] => string(10) "ERR_TEST_B"
      ["text"] => string(10) "Message #2"
      ["info"] => array(0) {
      }
      ["level"] => int(1024)
      ["class::code"] => string(18) "Test_B::ERR_TEST_B"
      ["trace"] => NULL
    }
  }
}