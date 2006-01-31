--TEST--
Solar_Cache{_File}::replace() -- a string value
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

$id = 'coyote';
$data = 'Wile E. Coyote';
$assert->isTrue($cache->replace($id, $data));
$assert->same($cache->fetch($id), $data);

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete