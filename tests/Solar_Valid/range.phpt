--TEST--
Solar_Valid::range()
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
$min = 4;
$max = 6;

// good
$test = array(
    4, 5, 6
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->range($val, $min, $max));
}

// bad, or are blank
$test = array(
    ' ', '',
    0, 1, 2, 3, 7, 8, 9,
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->range($val, $min, $max));
}

// blanks allowed
$test = array(
	"", ' ',
    4, 5, 6
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->range($val, $min, $max, Solar_Valid::OR_BLANK));
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
