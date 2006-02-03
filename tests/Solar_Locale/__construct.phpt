--TEST--
Solar_Locale::__construct()
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

// basic construction
$locale = Solar::factory('Solar_Locale');
$assert->isInstance($locale, 'Solar_Locale');

// initial config
$expect = array(
    'locale' => 'Solar/Locale/',
    'code'   => 'en_US',
);
$assert->property($locale, '_config', 'same', $expect);

// initial default code
$assert->same($locale->code(), 'en_US');

// are the translations loaded properly?  just check the country
// name instead of the whole array.
$expect = 'United States';
$assert->same($locale->string('Solar', 'FORMAT_COUNTRY'), $expect);

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
