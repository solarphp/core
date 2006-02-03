--TEST--
Solar_Valid::ipv4()
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
	'141.225.185.101',
	'255.0.0.0',
	'0.255.0.0',
	'0.0.255.0',
	'0.0.0.255',
	'127.0.0.1',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->ipv4($val));
}

// bad, or are blank
$test = array(
    ' ', '',
	'127.0.0.1234',
	'127.0.0.0.1',
	'256.0.0.0',
	'0.256.0.0',
	'0.0.256.0',
	'0.0.0.256',
	'1.',
	'1.2.',
	'1.2.3.',
	'1.2.3.4.',
	'a.b.c.d',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->ipv4($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'141.225.185.101',
	'255.0.0.0',
	'0.255.0.0',
	'0.0.255.0',
	'0.0.0.255',
	'127.0.0.1',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->ipv4($val, Solar_Valid::OR_BLANK));
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
