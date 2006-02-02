--TEST--
Solar_Cache{_File}::active()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

// should be active by default
$assert->isTrue($cache->active());

// turn it off
$cache->setActive(false);
$assert->isFalse($cache->active());

// turn it back on
$cache->setActive(true);
$assert->isTrue($cache->active());

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete