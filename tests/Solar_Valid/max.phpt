--TEST--
Solar_Valid::max()
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
$max = 3;

// good
$test = array(
    1, 2, 3,
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->max($val, $max));
}

// bad, or are blank
$test = array(
    ' ', '',
    4, 5, 6
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->max($val, $max));
}

// blanks allowed
$test = array(
	"", ' ',
	1, 2, 3,
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->max($val, $max, Solar_Valid::OR_BLANK));
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
