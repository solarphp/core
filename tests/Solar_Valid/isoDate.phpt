--TEST--
Solar_Valid::isoDate()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'0001-01-01',
	'1970-08-08',
	'1979-11-07',
	'2004-02-29',
	'9999-12-31',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoDate($val));
}

// bad, or are blank
$test = array(
    ' ', '',
    '1-2-3',
    '0001-1-1',
    '1-01-1',
    '1-1-01',
	'0000-00-00',
	'0000-01-01',
	'0010-20-40',
	'2005-02-29',
	'9999.12:31',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->isoDate($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'0001-01-01',
	'1970-08-08',
	'1979-11-07',
	'9999-12-31',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoDate($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
