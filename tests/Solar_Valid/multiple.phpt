--TEST--
Solar_Valid::multiple()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

$assert->fail('Incomplete test');

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete