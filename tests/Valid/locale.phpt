--TEST--
valid: locale()
--FILE--
<?php
require_once '../setup.php';
Solar::start();

$valid = Solar::object('Solar_Valid');

$test = array(
	'en_US',
	'pt_BR',
	'xx_YY',
	'PT_br',
	'EN_US',
	'12_34',
	'en_USA',
	'America/Chicago',
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = $valid->locale($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
string(5) "en_US"
bool(true)

string(5) "pt_BR"
bool(true)

string(5) "xx_YY"
bool(true)

string(5) "PT_br"
bool(false)

string(5) "EN_US"
bool(false)

string(5) "12_34"
bool(false)

string(6) "en_USA"
bool(false)

string(15) "America/Chicago"
bool(false)