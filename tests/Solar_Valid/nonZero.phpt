--TEST--
Solar_Valid::nonZero()
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

// good (are non-zero)
$test = array(
    '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
	'+-0.0',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->nonZero($val));
}

// bad (are in fact zero, or are blank)
$test = array(
    ' ', '',
    '0', 0, '00000.00', '+0', '-0', "+00.00",
);
foreach ($test as $key => $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->nonZero($val));
}


// blank
$test = array(
    ' ', '',
    '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
	'+-0.0',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->nonZero($val, Solar_Valid::OR_BLANK));
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
