--TEST--
Solar_Cache{_File}::delete()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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

// delete it, should not be able to fetch again
$cache->delete($id);
$assert->isFalse($cache->fetch($id));

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete