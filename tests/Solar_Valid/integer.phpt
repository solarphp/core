--TEST--
valid: integer()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	"-abc.123",
	"123.abc",
	"123,456",
	"+1234567890",
	'0000123.456000',
	1234567890,
	-123456789.0,
	-1234567890,
	'-123',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::integer($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(8) "-abc.123"
bool(false)

string(7) "123.abc"
bool(false)

string(7) "123,456"
bool(false)

string(11) "+1234567890"
bool(true)

string(14) "0000123.456000"
bool(false)

int(1234567890)
bool(true)

float(-123456789)
bool(true)

int(-1234567890)
bool(true)

string(4) "-123"
bool(true)