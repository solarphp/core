--TEST--
Solar_Exception (all methods)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
  #0 " . __FILE__ . "(16): Solar::factory('Solar_Exception', Array)
  #1 {main}";
  
$assert->same($e->__toString(), $expect);

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
