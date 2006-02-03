--TEST--
Solar_Cache{_File}::replace(string)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

require '_setup.php';

$id = 'coyote';
$data = 'Wile E. Coyote';
$assert->isTrue($cache->replace($id, $data));
$assert->same($cache->fetch($id), $data);

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
