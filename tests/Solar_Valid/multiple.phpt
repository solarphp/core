--TEST--
Solar_Valid::multiple()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');
$multi = array(
	array('min', 4),
	array('max', 7),
	'integer',
);

// good
$test = array(
	'4', 5, 6, '7'
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->multiple($val, $multi));
}

// bad, or are blank
$test = array(
    ' ', '',
	1, 2, 3, 5.5, 8, 9, 10
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->multiple($val, $multi));
}

// we don't test "allowed-blank" in multiple,
// because the different validations check for blanks
// in different ways.

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
