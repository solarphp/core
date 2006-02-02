--TEST--
Solar_Valid::inList()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
$test = $opts;
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inList($val, $opts));
}

// bad, or are blank
$test = array('a', 'b', 'c', '', ' ');
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->inList($val, $opts));
}


// blanks allowed
$test = $opts;
$test[] = "";
$test[] = " ";
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->inList($val, $opts, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
