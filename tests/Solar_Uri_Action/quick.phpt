--TEST--
Solar_Uri_Action::quick()
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

$uri = Solar::factory('Solar_Uri_Action');

// partial
$expect = '/index.php/controller/action/id?foo=bar';
$actual = $uri->quick($expect);
$assert->same($actual, $expect);

// full
$expect = 'http://example.com/index.php/controller/action?foo=bar';
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
