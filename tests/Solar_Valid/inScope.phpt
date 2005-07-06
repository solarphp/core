--TEST--
valid: inScope()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	"-abc.123",
	"123,456",
	"+1234567890",
	'0000123.456000',
	123.4560000,
	.1234567890,
	1.234567890,
	12.34567890,
	123.4567890,
	1234.567890,
	12345.67890,
	123456.7890,
	1234567.890,
	12345678.90,
	123456789.0,
	1234567890,
	-.1234567890,
	-1.234567890,
	-12.34567890,
	-123.4567890,
	-1234.567890,
	-12345.67890,
	-123456.7890,
	-1234567.890,
	-12345678.90,
	-123456789.0,
	-1234567890,
);

$opts = array(
	// size 10, scope 4
	10 => 4,
);

foreach ($opts as $size => $scope) {
	echo "Size $size, Scope $scope:\n";
	foreach ($test as $value) {
		Solar::dump($value);
		$result = Solar_Valid::inScope($value, $size, $scope);
		Solar::dump($result);
		echo "\n";
	}
	echo "\n";
}
?>
--EXPECT--
Size 10, Scope 4:
string(8) "-abc.123"
bool(false)

string(7) "123,456"
bool(false)

string(11) "+1234567890"
bool(true)

string(14) "0000123.456000"
bool(true)

float(123.456)
bool(true)

float(0.123456789)
bool(false)

float(1.23456789)
bool(false)

float(12.3456789)
bool(false)

float(123.456789)
bool(false)

float(1234.56789)
bool(false)

float(12345.6789)
bool(true)

float(123456.789)
bool(true)

float(1234567.89)
bool(true)

float(12345678.9)
bool(true)

float(123456789)
bool(true)

int(1234567890)
bool(true)

float(-0.123456789)
bool(false)

float(-1.23456789)
bool(false)

float(-12.3456789)
bool(false)

float(-123.456789)
bool(false)

float(-1234.56789)
bool(false)

float(-12345.6789)
bool(true)

float(-123456.789)
bool(true)

float(-1234567.89)
bool(true)

float(-12345678.9)
bool(true)

float(-123456789)
bool(true)

int(-1234567890)
bool(true)