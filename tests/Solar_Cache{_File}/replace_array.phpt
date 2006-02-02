--TEST--
Solar_Cache{_File}::replace(array)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
