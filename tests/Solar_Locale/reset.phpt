--TEST--
Solar_Locale::reset()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
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
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
