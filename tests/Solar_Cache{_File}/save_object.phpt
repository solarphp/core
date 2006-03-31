--TEST--
Solar_Cache{_File}::save(object)
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

class Coyote extends Solar_Base {
	public $acme = 'Acme Industries, LLC';
	protected $meal = 'Roadrunner';
}

$id = 'coyote';
$data = new Coyote();
$assert->isTrue($cache->save($id, $data));
$assert->equals($cache->fetch($id), $data);

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
