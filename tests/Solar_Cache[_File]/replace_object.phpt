--TEST--
Solar_Cache{_File}::replace() -- an object
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

require '_setup.php';

class Coyote extends Solar_Base {
	public $acme = 'Acme Industries, LLC';
	protected $meal = 'Roadrunner';
}

$id = 'coyote';
$data = new Coyote();
$assert->isTrue($cache->replace($id, $data));
$assert->equals($cache->fetch($id), $data);

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete