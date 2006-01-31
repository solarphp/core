--TEST--
Solar_Valid::integer()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	"+1234567890",
	1234567890,
	-123456789.0,
	-1234567890,
	'-123',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->integer($val));
}

// bad, or are blank
$test = array(
    ' ', '',
	"-abc.123",
	"123.abc",
	"123,456",
	'0000123.456000',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->integer($val));
}


// blanks allowed
$test = array(
    "", ' ',
	"+1234567890",
	1234567890,
	-123456789.0,
	-1234567890,
	'-123',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->integer($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete