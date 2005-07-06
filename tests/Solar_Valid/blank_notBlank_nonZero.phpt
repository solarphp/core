--TEST--
valid: blank(), notBlank(), nonZero()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	"",
	"  ",
	"\t\n \r",
	"foo",
	0,
	"0"
);

echo "Blank:\n";

foreach ($test as $value) {
	$result = Solar_Valid::blank($value);
	Solar::dump($result);
}

echo "\nNot Blank:\n";

foreach ($test as $value) {
	$result = Solar_Valid::notBlank($value);
	Solar::dump($result);
}	

echo "\nNonzero:\n";

foreach ($test as $value) {
	$result = Solar_Valid::nonZero($value);
	Solar::dump($result);
}	
?>
--EXPECT--
Blank:
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

Not Blank:
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(true)

Nonzero:
bool(true)
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)