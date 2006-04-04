--TEST--
Solar_Uri_Public::quick()
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

$uri = Solar::factory('Solar_Uri_Public');

// partial
$expect = 'Solar/styles/default.css';
$actual = $uri->quick($expect);
$assert->same($actual, "/public/$expect");

// full
$expect = 'http://example.com/public/Solar/styles/default.css';
$actual = $uri->quick($expect, true);
$assert->same($actual, $expect);

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
