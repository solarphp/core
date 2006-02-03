--TEST--
Solar_Valid::alpha()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'alphaonly',
	'AlphaOnLy',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->alpha($val));
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
    $assert->isFalse($valid->alpha($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'alphaonly',
	'AlphaOnLy',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->alpha($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
