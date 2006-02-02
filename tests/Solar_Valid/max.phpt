--TEST--
Solar_Valid::max()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
