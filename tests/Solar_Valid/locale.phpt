--TEST--
Solar_Valid::locale()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'en_US',
	'pt_BR',
	'xx_YY',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->locale($val));
}

// bad, or are blank
$test = array(
    ' ', '',
	'PT_br',
	'EN_US',
	'12_34',
	'en_USA',
	'America/Chicago',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->locale($val));
}


// blanks allowed
$test = array(
    "", ' ',
	'en_US',
	'pt_BR',
	'xx_YY',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->locale($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
