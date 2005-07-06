--TEST--
valid: isoDate()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	'0000-00-00',
	'0000-01-01',
	'0001-01-01',
	'0010-20-40',
	'1970-08-08',
	'1979-11-07',
	'2005-02-29',
	'9999-12-31',
	'9999.12:31',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::isoDate($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(10) "0000-00-00"
bool(false)

string(10) "0000-01-01"
bool(false)

string(10) "0001-01-01"
bool(true)

string(10) "0010-20-40"
bool(false)

string(10) "1970-08-08"
bool(true)

string(10) "1979-11-07"
bool(true)

string(10) "2005-02-29"
bool(false)

string(10) "9999-12-31"
bool(true)

string(10) "9999.12:31"
bool(false)