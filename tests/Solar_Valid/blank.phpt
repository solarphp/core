--TEST--
Solar_Valid::blank()
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
