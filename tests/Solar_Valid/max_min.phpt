--TEST--
valid: max() and min()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	"-2.28",
	-1,
	0,
	'',
	"1",
	2,
	"3.14",
	4,
	5,
);

$max = 3;
echo "Max $max:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::max($value, $max);
	Solar::dump($result);
	echo "\n";
}

$min = 2;
echo "\nMin $min:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::min($value, $min);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
Max 3:
string(5) "-2.28"
bool(true)

int(-1)
bool(true)

int(0)
bool(true)

string(0) ""
bool(false)

string(1) "1"
bool(true)

int(2)
bool(true)

string(4) "3.14"
bool(false)

int(4)
bool(false)

int(5)
bool(false)


Min 2:
string(5) "-2.28"
bool(false)

int(-1)
bool(false)

int(0)
bool(false)

string(0) ""
bool(false)

string(1) "1"
bool(false)

int(2)
bool(true)

string(4) "3.14"
bool(true)

int(4)
bool(true)

int(5)
bool(true)