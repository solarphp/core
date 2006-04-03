--TEST--
Solar_PathStack::find()
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

// get the stack object FIRST
$stack = Solar::factory('Solar_PathStack');

// NOW reset the include_path
$old_path = set_include_path(dirname(dirname(__FILE__)));

// use the testing directory to look for __construct.phpt files
$dir = dirname(dirname(__FILE__));
$path = array(
    "Solar_Base",
    "Solar_Debug_Timer",
    "Solar_Debug_Var",
);

$stack->set($path);

// should find it at Solar_Base
$actual = $stack->find('__construct.phpt');
$assert->same($actual, "{$path[0]}/__construct.phpt");

// should find it at Solar_Debug_Timer
$actual = $stack->find('start.phpt');
$assert->same($actual, "{$path[1]}/start.phpt");

// should find it at Solar_Debug_Var
$actual = $stack->find('dump.phpt');
$assert->same($actual, "{$path[2]}/dump.phpt");

// should not find it at all
$actual = $stack->find('no_such_file');
$assert->isFalse($actual);

// put the include_path back
set_include_path($old_path);

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
