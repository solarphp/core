--TEST--
valid: maxLength() and minLength()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	"",
	0,
	"I am",
	"I am the very model",
	"I am the very model of a modern",
	"I am the very model of a moden Major-General",
);

$len = strlen("I am the very model");

echo "Max Length $len:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::maxLength($value, $len);
	Solar::dump($result);
	echo "\n";
}

echo "Min Length $len:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::minLength($value, $len);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
Max Length 19:
string(0) ""
bool(true)

int(0)
bool(true)

string(4) "I am"
bool(true)

string(19) "I am the very model"
bool(true)

string(31) "I am the very model of a modern"
bool(false)

string(44) "I am the very model of a moden Major-General"
bool(false)

Min Length 19:
string(0) ""
bool(false)

int(0)
bool(false)

string(4) "I am"
bool(false)

string(19) "I am the very model"
bool(true)

string(31) "I am the very model of a modern"
bool(true)

string(44) "I am the very model of a moden Major-General"
bool(true)