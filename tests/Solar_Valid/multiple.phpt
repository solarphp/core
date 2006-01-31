--TEST--
Solar_Valid::multiple()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$assert->fail('Incomplete test');

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete