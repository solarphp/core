--TEST--
Solar_Valid::inKeys()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// basic options
$opts = array(
	0      => 'val0',
	1      => 'val1',
	'key0' => 'val3',
	'key1' => 'val4',
	'key2' => 'val5'
);


// good
$test = array_keys($opts);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inKeys($val, $opts));
}

// bad, or are blank
$test = array('a', 'b', 'c', '', ' ');
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->inKeys($val, $opts));
}


// blanks allowed
$test = array_keys($opts);
$test[] = " ";
$test[] = "\r";
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inKeys($val, $opts, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete