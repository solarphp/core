--TEST--
Solar_Exception::__construct()
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

$e = Solar::factory('Solar_Exception', $config);
$assert->isInstance($e, 'Solar_Exception');
$assert->property($e, '_class', 'same', $config['class']);
$assert->property($e, 'code', 'same', $config['code']);
$assert->property($e, 'message', 'same', $config['text']);
$assert->property($e, '_info', 'same', $config['info']);

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
