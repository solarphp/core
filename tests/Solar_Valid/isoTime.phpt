--TEST--
Solar_Valid::isoTime()
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
	'00:00:00',
	'12:34:56',
	'23:59:59',
	'24:00:00',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoTime($val));
}

// bad, or are blank
$test = array(
    ' ', '',
	'24:00:01',
	'12.00.00',
	'12-34_56',
	' 12:34:56 ',
	'  :34:56',
	'12:  :56',
	'12:34   ',
	'12:34'
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->isoTime($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'00:00:00',
	'12:34:56',
	'23:59:59',
	'24:00:00',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoTime($val, Solar_Valid::OR_BLANK));
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
