--TEST--
Solar_Locale::reset()
--FILE---
<?php
require '../_prepend.php';
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
require '../_append.php';
?>
--EXPECT--
test complete