--TEST--
Solar_Valid::inScope()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

$good = array(
	"+1234567890",
	'0000123.456000',
	123.4560000,
	12345.67890,
	123456.7890,
	1234567.890,
	12345678.90,
	123456789.0,
	1234567890,
	-12345.67890,
	-123456.7890,
	-1234567.890,
	-12345678.90,
	-123456789.0,
	-1234567890,
);

$bad = array(
    ' ', '',
	"-abc.123",
	"123,456",
	.1234567890,
	1.234567890,
	12.34567890,
	123.4567890,
	1234.567890,
	-.1234567890,
	-1.234567890,
	-12.34567890,
	-123.4567890,
	-1234.567890,
);

$size = 10;
$scope = 4;

// good
foreach ($good as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inScope($val, $size, $scope));
}

// bad, or are blank
foreach ($bad as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->inScope($val, $size, $scope));
}

// blanks allowed
$test = $good;
$test[] = "";
$test[] = " ";
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inScope($val, $size, $scope, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
