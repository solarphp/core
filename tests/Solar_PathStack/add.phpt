--TEST--
Solar_PathStack::add()
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


// add to the stack as an array
$stack = Solar::factory('Solar_PathStack');
$stack->add(array('/path/1', '/path/2', '/path/3'));

$expect = array(
  "/path/1/",
  "/path/2/",
  "/path/3/",
);
$assert->setLabel('by array');
$assert->same($stack->get(), $expect);

// add to the stack as a shell pathspec
$stack = Solar::factory('Solar_PathStack');
$stack->add('/path/1:/path/2:/path/3');

$expect = array(
  "/path/1/",
  "/path/2/",
  "/path/3/",
);
$assert->setLabel('by string');
$assert->same($stack->get(), $expect);

// add to the stack as LIFO singles
$stack = Solar::factory('Solar_PathStack');
$stack->add('/path/1');
$stack->add('/path/2');
$stack->add('/path/3');

$expect = array(
  "/path/3/",
  "/path/2/",
  "/path/1/",
);
$assert->setLabel('by lifo singles');
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
