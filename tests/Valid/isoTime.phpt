--TEST--
valid: isoTime()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	'00:00:00',
	'12:34:56',
	'23:59:59',
	'24:00:00',
	'24:00:01',
	'12.00.00',
	'12-34_56',
	' 12:34:56 ',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::isoTime($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(8) "00:00:00"
bool(true)

string(8) "12:34:56"
bool(true)

string(8) "23:59:59"
bool(true)

string(8) "24:00:00"
bool(true)

string(8) "24:00:01"
bool(false)

string(8) "12.00.00"
bool(false)

string(8) "12-34_56"
bool(false)

string(10) " 12:34:56 "
bool(false)