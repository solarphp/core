--TEST--
valid: custom()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	1, 2, 3, '4', 5, 5.5, 6, '7', 8, 9, 10
);

$callback = 'is_int';

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::custom($value, $callback);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
int(1)
bool(true)

int(2)
bool(true)

int(3)
bool(true)

string(1) "4"
bool(false)

int(5)
bool(true)

float(5.5)
bool(false)

int(6)
bool(true)

string(1) "7"
bool(false)

int(8)
bool(true)

int(9)
bool(true)

int(10)
bool(true)