--TEST--
Solar_Valid::alnum()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete