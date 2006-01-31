--TEST--
Solar_Locale::loaded()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

// Solar should be loaded to begin with
$locale = Solar::factory('Solar_Locale');
$assert->same($locale->code(), 'en_US');
$assert->isTrue($locale->loaded('Solar'));

// not loaded, then loaded
$assert->isFalse($locale->loaded('Solar_Test_Example'));
$locale->load('Solar_Test_Example', 'Solar/Test/Example/Locale/');
$assert->isTrue($locale->loaded('Solar_Test_Example'));

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete