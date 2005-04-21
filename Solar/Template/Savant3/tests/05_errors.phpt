--TEST--
error reporting
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);

// basic errors
$result = $tpl->error(
	'ERR_UNKNOWN',
	array(
		'type' => 'Savant_Error',
		'key1' => 'val1',
		'key2' => 'val2'
	),
	false // no trace
);
dump($result);

// PHP5 exceptions
$tpl->setErrorType('exception');
try {
	$result = $tpl->error(
		'ERR_UNKNOWN',
		array(
			'type' => 'Savant_Error_exception',
			'key1' => 'val1',
			'key2' => 'val2'
		),
		false // no trace
	);
	dump($result);
} catch (Savant3_Exception $e) {
	echo "\nCaught exception.\n\n";
}


// PEAR errors
// set up for PEAR first
$path = ini_get('include_path');
ini_set('include_path', $path .':./resources/');
ini_set('error_reporting', E_ALL);

// try the error
$tpl->setErrorType('pear');
$result = $tpl->error(
	'ERR_UNKNOWN',
	array(
		'type' => 'Savant_Error_pear',
		'key1' => 'val1',
		'key2' => 'val2'
	),
	false // no trace
);
dump($result);

?>
--EXPECT--
object(Savant3_Error)#2 (4) {
  ["code"] => string(11) "ERR_UNKNOWN"
  ["info"] => array(3) {
    ["type"] => string(12) "Savant_Error"
    ["key1"] => string(4) "val1"
    ["key2"] => string(4) "val2"
  }
  ["text"] => string(22) "Savant3: unknown error"
  ["trace"] => bool(false)
}

Caught exception.

object(Savant3_Error_pear)#3 (4) {
  ["code"] => string(11) "ERR_UNKNOWN"
  ["info"] => array(3) {
    ["type"] => string(17) "Savant_Error_pear"
    ["key1"] => string(4) "val1"
    ["key2"] => string(4) "val2"
  }
  ["text"] => string(22) "Savant3: unknown error"
  ["trace"] => bool(false)
}
