--TEST--
Solar_Exception (all methods in generic class)
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

$config = array(
    'class' => 'Solar_Test_Example',
    'code'  => 'ERR_CODE',
    'text'  => 'Error message',
    'info'  => array(
        'foo' => 'bar',
        'baz' => 'dib',
        'zim' => 'gir',
    ),
);

$e = Solar::factory('Solar_Exception', $config);

// construction
$assert->isInstance($e, 'Solar_Exception');

// internals
$assert->property($e, '_class', 'same', $config['class']);
$assert->property($e, 'code', 'same', $config['code']);
$assert->property($e, 'message', 'same', $config['text']);
$assert->property($e, '_info', 'same', $config['info']);

// custom add-on methods
$assert->same($e->getInfo(), $config['info']);
$assert->same($e->getClass(), $config['class']);

// custom __toString() output with stack trace
$expect = "exception 'Solar_Exception'
class::code 'Solar_Test_Example::ERR_CODE' 
with message 'Error message' 
information array (
  'foo' => 'bar',
  'baz' => 'dib',
  'zim' => 'gir',
) 
Stack trace:
  #0 " . __FILE__ . "(25): Solar::factory('Solar_Exception', Array)
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
