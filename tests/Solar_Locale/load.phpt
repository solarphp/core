--TEST--
Solar_Locale::load()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------


// basic construction
$locale = Solar::factory('Solar_Locale');
$assert->same($locale->code(), 'en_US');

// load Solar_Test_Example strings (a known existing file)
$flag = $locale->load('Solar_Test_Example', 'Solar/Test/Example/Locale/');
$assert->isTrue($flag);

// test if the strings were loaded
$actual = $locale->string('Solar_Test_Example', 'HELLO_WORLD');
$assert->same($actual, "hello world");

// load strings for a non-existent locale dir
$flag = $locale->load('Solar_Test_Example', 'Solar/Test/Example/Locale/NoSuch');
$assert->isFalse($flag);

// test that locale returns the key, not a translation
$actual = $locale->string('Solar_Test_Example', 'HELLO_WORLD');
$assert->same($actual, "HELLO_WORLD");


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
