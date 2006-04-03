--TEST--
Solar_PathStack::findReal()
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

// use the testing directory to look for __construct.phpt files
$dir = dirname(dirname(__FILE__));
$path = array(
    "$dir/Solar_Base",
    "$dir/Solar_Debug_Timer",
    "$dir/Solar_Debug_Var",
);

$stack = Solar::factory('Solar_PathStack');
$stack->set($path);

// should find it at Solar_Base
$actual = $stack->findReal('__construct.phpt');
$assert->same($actual, "{$path[0]}/__construct.phpt");

// should find it at Solar_Debug_Timer
$actual = $stack->findReal('start.phpt');
$assert->same($actual, "{$path[1]}/start.phpt");

// should find it at Solar_Debug_Var
$actual = $stack->findReal('dump.phpt');
$assert->same($actual, "{$path[2]}/dump.phpt");

// should not find it at all
$actual = $stack->findReal('no_such_file');
$assert->isFalse($actual);

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
