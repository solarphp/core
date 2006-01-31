--TEST--
Solar_Cache{_File}::life()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

$id = 'coyote';
$data = 'Wile E. Coyote';

// configured from setup
$assert->same($cache->life(), $config['options']['life']);

// store something
$assert->isTrue($cache->replace($id, $data));
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
require '../_append.php';
?>
--EXPECT--
test complete