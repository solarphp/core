--TEST--
Solar_Valid::alnum()
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
	0, 1, 2, 5,
	'0', '1', '2', '5',
	'alphaonly',
	'AlphaOnLy',
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->alnum($val));
}

// bad, or are blank
$test = array(
	"", '',
	"Seven 8 nine",
	"non:alpha-numeric's",
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->alnum($val));
}

// blanks allowed
$test = array(
	"", ' ',
	0, 1, 2, 5,
	'0', '1', '2', '5',
	'alphaonly',
	'AlphaOnLy',
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->alnum($val, Solar_Valid::OR_BLANK));
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
