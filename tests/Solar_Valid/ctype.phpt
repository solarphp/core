--TEST--
Solar_Valid::ctype()
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
	'alphaonly',
	'AlphaOnLy',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->ctype($val, 'alpha'));
}

// bad, or are blank
$test = array(
    ' ', '',
	0, 1, 2, 5,
	'0', '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->ctype($val, 'alpha'));
}


// blanks allowed
$test = array(
    "", ' ',
	'alphaonly',
	'AlphaOnLy',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->ctype($val, 'alpha', Solar_Valid::OR_BLANK));
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
