--TEST--
Solar_Cache{_File}::fetch()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

$id = 'coyote';
$data = 'Wile E. Coyote';

// data has not been stored yet
$assert->isFalse($cache->fetch($id));

// store it
$assert->isTrue($cache->replace($id, $data));

// and we should be able to fetch now
$assert->same($cache->fetch($id), $data);

// deactivate then try to fetch
$cache->setActive(false);
$assert->isFalse($cache->active());
$assert->isFalse($cache->fetch($id));

// re-activate then try to fetch
$cache->setActive(true);
$assert->isTrue($cache->active());
$assert->same($cache->fetch($id), $data);

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete