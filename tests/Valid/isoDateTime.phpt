--TEST--
valid: isoDateTime()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	'0000-00-00T00:00:00',
	'0000-01-01T12:34:56',
	'0001-01-01T23:59:59',
	'0010-20-40T12:34:56',
	'1970-08-08t12:34:56',
	'1979-11-07T12:34',
	'2005-02-29T24:00:00',
	'           24:00:00',
	'          T        ',
	'9999-12-31         ',
	'9999.12:31 ab:cd:ef',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::isoDateTime($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(19) "0000-00-00T00:00:00"
bool(false)

string(19) "0000-01-01T12:34:56"
bool(false)

string(19) "0001-01-01T23:59:59"
bool(true)

string(19) "0010-20-40T12:34:56"
bool(false)

string(19) "1970-08-08t12:34:56"
bool(false)

string(16) "1979-11-07T12:34"
bool(false)

string(19) "2005-02-29T24:00:00"
bool(false)

string(19) "           24:00:00"
bool(false)

string(19) "          T        "
bool(false)

string(19) "9999-12-31         "
bool(false)

string(19) "9999.12:31 ab:cd:ef"
bool(false)