--TEST--
Solar_Locale::string()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

// load the Solar_Test_Example strings
$locale = Solar::factory('Solar_Locale');
$assert->same($locale->code(), 'en_US');
$locale->load('Solar_Test_Example', 'Solar/Test/Example/Locale/');
$assert->isTrue($locale->loaded('Solar_Test_Example'));

// 0
$assert->same(
    $locale->string('Solar_Test_Example', 'APPLE', 0),
    'apples'
);

// not specified, should be 1
$assert->same(
    $locale->string('Solar_Test_Example', 'APPLE'),
    'apple'
);

// 1
$assert->same(
    $locale->string('Solar_Test_Example', 'APPLE', 1),
    'apple'
);

// 2 or more
$assert->same(
    $locale->string('Solar_Test_Example', 'APPLE', 2),
    'apples'
);
$assert->same(
    $locale->string('Solar_Test_Example', 'APPLE', 5),
    'apples'
);

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
