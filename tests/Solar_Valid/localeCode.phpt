--TEST--
Solar_Valid::localeCode()
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
	'en_US',
	'pt_BR',
	'xx_YY',
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->localeCode($val));
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
    $assert->isFalse($valid->localeCode($val));
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
    $assert->isTrue($valid->localeCode($val, Solar_Valid::OR_BLANK));
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
