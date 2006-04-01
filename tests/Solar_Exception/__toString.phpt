--TEST--
Solar_Exception::__toString()
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

$e = Solar::factory('Solar_Exception', $config);

$expect = "exception 'Solar_Exception'
class::code 'Solar_Test_Example::ERR_CODE' 
with message 'Error message' 
information array (
  'foo' => 'bar',
  'baz' => 'dib',
  'zim' => 'gir',
) 
Stack trace:
  #0 " . __FILE__ . "(14): Solar::factory('Solar_Exception', Array)
  #1 {main}";
  
$assert->same($e->__toString(), $expect);

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
