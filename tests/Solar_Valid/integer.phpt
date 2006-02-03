--TEST--
Solar_Valid::integer()
--FILE---
<?php
// include ../_prepend.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_prepend.inc')) {
    require dirname(dirname(__FILE__)) . '/_prepend.inc';
}

// include ./_prepend.inc
if (is_readable(dirname(__FILE__) . '/_prepend.inc')) {
    require dirname(__FILE__) . '/_prepend.inc';
}

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

// include ./_append.inc
if (is_readable(dirname(__FILE__) . '/_append.inc')) {
    require dirname(__FILE__) . '/_append.inc';
}
// include ../_append.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_append.inc')) {
    require dirname(dirname(__FILE__)) . '/_append.inc';
}
?>
--EXPECT--
