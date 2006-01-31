--TEST--
Solar_Valid::nonZero()
--FILE---
<?php
require '../_prepend.php';
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
require '../_append.php';
?>
--EXPECT--
test complete