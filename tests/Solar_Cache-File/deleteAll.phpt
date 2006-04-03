--TEST--
Solar_Cache-File::deleteAll()
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

$list = array(1, 2, 'five');
$data = 'Wile E. Coyote';

foreach ($list as $id) {
    // data has not been stored yet
    $assert->isFalse($cache->fetch($id));
    // so store some data
    $assert->isTrue($cache->save($id, $data));
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
