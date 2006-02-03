--TEST--
Solar_Valid::multiple()
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
