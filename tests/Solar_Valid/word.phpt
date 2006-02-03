--TEST--
Solar_Valid::word()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'abc', 'def', 'ghi',
	'abc_def',
	'A1s_2Sd',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->word($val));
}

// bad, or are blank
$test = array(
	"", '',
	'a,', '^b', '%',
	'ab-db cd-ef',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->word($val));
}

// blanks allowed
$test = array(
	"", ' ',
	'abc', 'def', 'ghi',
	'abc_def',
	'A1s_2Sd',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->word($val, Solar_Valid::OR_BLANK));
}


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
