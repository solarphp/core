--TEST--
Solar_Cache{_File}::fetch()
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
