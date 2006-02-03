--TEST--
Solar_Valid::min()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');
$min = 4;

// good
$test = array(
    4, 5, 6
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->min($val, $min));
}

// bad, or are blank
$test = array(
    ' ', '',
    0, 1, 2, 3, ' ', ''
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->min($val, $min));
}

// blanks allowed
$test = array(
	"", ' ',
    4, 5, 6
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->min($val, $min, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
