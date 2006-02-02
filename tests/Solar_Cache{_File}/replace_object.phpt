--TEST--
Solar_Cache{_File}::replace(object)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
