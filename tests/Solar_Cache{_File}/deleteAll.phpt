--TEST--
Solar_Cache{_File}::deleteAll()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

$list = array(1, 2, 'five');
$data = 'Wile E. Coyote';

foreach ($list as $id) {
    // data has not been stored yet
    $assert->isFalse($cache->fetch($id));
    // so store some data
    $assert->isTrue($cache->replace($id, $data));
    // and we should be able to fetch now
    $assert->same($cache->fetch($id), $data);
}

// delete everything
$cache->deleteAll();

// should not be able to fetch again
foreach ($list as $id) {
    $assert->isFalse($cache->fetch($id));
}

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete