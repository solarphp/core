--TEST--
Solar_Valid::feedback()
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

$valid = Solar::factory('Solar_Valid');
$method = 'range';
$message = 'INVALID_NUMBER';
$min = 4;
$max = 6;
$params = array($method, $message, $min, $max);

// test that a valid value returns null
$result = $valid->feedback(5, $params);
$assert->isNull($result);

// test that an invalid value returns the message
$result = $valid->feedback(1, $params);
$assert->same($result, $message);

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
