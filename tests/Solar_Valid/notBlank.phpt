--TEST--
Solar_Valid::notBlank()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	0, 1, 2, 5,
	'0', '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->notBlank($val));
}

// bad
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
    $assert->isFalse($valid->notBlank($val));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
