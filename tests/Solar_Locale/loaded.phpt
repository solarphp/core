--TEST--
Solar_Locale::loaded()
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

// Solar should be loaded to begin with
$locale = Solar::factory('Solar_Locale');
$assert->same($locale->code(), 'en_US');
$assert->isTrue($locale->loaded('Solar'));

// not loaded, then loaded
$assert->isFalse($locale->loaded('Solar_Test_Example'));
$locale->load('Solar_Test_Example', 'Solar/Test/Example/Locale/');
$assert->isTrue($locale->loaded('Solar_Test_Example'));

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
