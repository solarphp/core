--TEST--
Solar_Valid::isoDateTime()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'0001-01-01T00:00:00',
    '1970-08-08T12:34:56',
	'2004-02-29T24:00:00',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoDateTime($val));
}

// bad, or are blank
$test = array(
    ' ', '',
	'0000-00-00T00:00:00',
	'0000-01-01T12:34:56',
	'0010-20-40T12:34:56',
	'1979-11-07T12:34',
	'1970-08-08t12:34:56',
	'           24:00:00',
	'          T        ',
	'9999-12-31         ',
	'9999.12:31 ab:cd:ef',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->isoDateTime($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'0001-01-01T00:00:00',
    '1970-08-08T12:34:56',
	'2004-02-29T24:00:00',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->isoDateTime($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
