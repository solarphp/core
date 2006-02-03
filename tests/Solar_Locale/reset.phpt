--TEST--
Solar_Locale::reset()
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


// initial default code
$locale = Solar::factory('Solar_Locale');
$assert->same($locale->code(), 'en_US');

// set to a new code
$locale->reset('fr_FR');
$assert->same($locale->code(), 'fr_FR');

// were new translations loaded? just check the FORMAT_COUNTRY string
// instead of the whole array.
$assert->same($locale->string('Solar', 'FORMAT_COUNTRY'), 'France');


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
