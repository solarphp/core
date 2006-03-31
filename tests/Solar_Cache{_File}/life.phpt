--TEST--
Solar_Cache{_File}::life()
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

require '_setup.php';

$id = 'coyote';
$data = 'Wile E. Coyote';

// configured from setup
$assert->same($cache->life(), $config['life']);

// store something
$assert->isTrue($cache->save($id, $data));
$assert->same($cache->fetch($id), $data);

// wait until just before the lifetime,
// we should still get data
sleep($cache->life() - 1);
$assert->same($cache->fetch($id), $data);

// wait until just after the lifetime,
// we should get nothing
sleep(2);
$assert->isFalse($cache->fetch($id));

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
