--TEST--
Solar_Valid::rangeLength()
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
$min = 4;
$max = 6;

// good
$test = array(
	"abcd",
	"abcde",
	"abcdef",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->rangeLength($val, $min, $max));
}

// bad, or are blank
$test = array(
	"", " ",
	'a', 'ab', 'abc',
	'abcdefg', 'abcdefgh', 'abcdefghi', 
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->rangeLength($val, $min, $max));
}

// blanks allowed
$test = array(
	"", ' ',
	"abcd",
	"abcde",
	"abcdef",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->rangeLength($val, $min, $max, Solar_Valid::OR_BLANK));
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
