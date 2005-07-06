--TEST--
valid: alpha() and alphanumeric()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	0,
	"0",
	"alphaonly",
	"AlphaOnLy",
	" Something 8 else",
	"someThing8else",
	"non:alpha-numberic's",
);

echo "Alpha:\n";

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::alpha($value);
	Solar::dump($result);
}

echo "\nAlphanumeric:\n";

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::alphanumeric($value);
	Solar::dump($result);
}	
?>
--EXPECT--
Alpha:
int(0)
bool(false)
string(1) "0"
bool(false)
string(9) "alphaonly"
bool(true)
string(9) "AlphaOnLy"
bool(true)
string(17) " Something 8 else"
bool(false)
string(14) "someThing8else"
bool(false)
string(20) "non:alpha-numberic's"
bool(false)

Alphanumeric:
int(0)
bool(true)
string(1) "0"
bool(true)
string(9) "alphaonly"
bool(true)
string(9) "AlphaOnLy"
bool(true)
string(17) " Something 8 else"
bool(false)
string(14) "someThing8else"
bool(true)
string(20) "non:alpha-numberic's"
bool(false)