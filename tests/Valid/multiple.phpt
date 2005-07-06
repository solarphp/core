--TEST--
valid: multiple()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	1, 2, 3, '4', 5, 5.5, 6, '7', 8, 9, 10
);

$multi = array(
	array('min', 4),
	array('max', 7),
	array('integer'),
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::multiple($value, $multi);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
int(1)
bool(false)

int(2)
bool(false)

int(3)
bool(false)

string(1) "4"
bool(true)

int(5)
bool(true)

float(5.5)
bool(false)

int(6)
bool(true)

string(1) "7"
bool(true)

int(8)
bool(false)

int(9)
bool(false)

int(10)
bool(false)