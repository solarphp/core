--TEST--
Solar_Cache{_File}::replace() -- an array value
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

$id = 'coyote';
$data = array(
    'name' => 'Wile E.',
    'type' => 'Coyote',
    'eats' => 'Roadrunner',
    'flag' => 'Not again!',
);
$assert->isTrue($cache->replace($id, $data));
$assert->same($cache->fetch($id), $data);

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete