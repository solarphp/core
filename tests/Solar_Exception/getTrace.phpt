--TEST--
Solar_Exception::getTrace()
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

$expect = array(
  0 => array(
    'file' => __FILE__,
    'line' => 14,
    'function' => 'factory',
    'class' => 'Solar',
    'type' => '::',
    'args' => array(
      0 => 'Solar_Exception',
      1 => array(
        'class' => 'Solar_Test_Example',
        'code' => 'ERR_CODE',
        'text' => 'Error message',
        'info' => array(
          'foo' => 'bar',
          'baz' => 'dib',
          'zim' => 'gir',
        ),
      ),
    ),
  ),
);

$assert->same($e->getTrace(), $expect);

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
