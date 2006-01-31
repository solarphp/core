--TEST--
Solar_Valid::blank()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'empty'   => "",
	'space'   => " ",
	'tab'     => "\t",
	'newline' => "\n",
	'return'  => "\r",
	'multi'   => " \t \n \r ",
);
foreach ($test as $key => $val) {
    $assert->setLabel($key);
    $assert->isTrue($valid->blank($val));
}

// bad
$test = array(
	0, 1, 2, 5,
	'0', '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->blank($val));
}



// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete