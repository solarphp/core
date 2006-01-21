--TEST--
valid: ipv4()
--FILE--
<?php
require_once '../setup.php';
Solar::start();

$valid = Solar::factory('Solar_Valid');

$test = array(
	'141.225.185.101',
	'255.0.0.0',
	'0.255.0.0',
	'0.0.255.0',
	'0.0.0.255',
	'127.0.0.1',
	'127.0.0.1234',
	'127.0.0.0.1',
	'256.0.0.0',
	'0.256.0.0',
	'0.0.256.0',
	'0.0.0.256',
	'1.',
	'1.2.',
	'1.2.3.',
	'1.2.3.4.',
	'a.b.c.d',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = $valid->ipv4($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(15) "141.225.185.101"
bool(true)

string(9) "255.0.0.0"
bool(true)

string(9) "0.255.0.0"
bool(true)

string(9) "0.0.255.0"
bool(true)

string(9) "0.0.0.255"
bool(true)

string(9) "127.0.0.1"
bool(true)

string(12) "127.0.0.1234"
bool(false)

string(11) "127.0.0.0.1"
bool(false)

string(9) "256.0.0.0"
bool(false)

string(9) "0.256.0.0"
bool(false)

string(9) "0.0.256.0"
bool(false)

string(9) "0.0.0.256"
bool(false)

string(2) "1."
bool(false)

string(4) "1.2."
bool(false)

string(6) "1.2.3."
bool(false)

string(8) "1.2.3.4."
bool(false)

string(7) "a.b.c.d"
bool(false)