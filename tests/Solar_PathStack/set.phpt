--TEST--
Solar_PathStack::set()
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

$expect = array(
  '/path/1/',
  '/path/2/',
  '/path/3/',
);

// set the stack as string
$stack = Solar::factory('Solar_PathStack');
$stack->set('/path/1:/path/2:/path/3');
$assert->same($stack->get(), $expect);

// set the stack as array
$stack = Solar::factory('Solar_PathStack');
$stack->set($expect);
$assert->same($stack->get(), $expect);


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
