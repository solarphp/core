--TEST--
Solar_Valid::sepWords()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'abc def ghi',
	' abc def ',
	'a1s_2sd and another',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->sepWords($val));
}

// bad, or are blank
$test = array(
	"", '',
	'a, b, c',
	'ab-db cd-ef',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->sepWords($val));
}

// blanks allowed
$test = array(
	"", ' ',
	'abc def ghi',
	' abc def ',
	'a1s_2sd and another',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->sepWords($val, ' ', Solar_Valid::OR_BLANK));
}

// alternative separator
$test = array(
	'abc,def,ghi',
	'abc,def',
	'a1s_2sd,and,another',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->sepWords($val, ','));
}


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
