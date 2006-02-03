--TEST--
Solar_Debug_Var (all tests)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

// PLAIN TEXT

$var = Solar::factory('Solar_Debug_Var', array('output' => 'text'));

// dump an object
$expectObject = <<<EXPECT
object(Solar_Test_Example)#10 (1) {
  ["_config:protected"] => array(4) {
    ["foo"] => string(3) "bar"
    ["baz"] => string(3) "dib"
    ["zim"] => string(3) "gaz"
    ["locale"] => string(26) "Solar/Test/Example/Locale/"
  }
}

EXPECT;

$object = Solar::factory('Solar_Test_Example');
$assert->setLabel('object as text');
$assert->same($var->dump($object), $expectObject);


// dump an array
$expectArray = <<<EXPECT
array(3) {
  ["foo"] => string(3) "bar"
  ["baz"] => string(3) "dib"
  ["zim"] => array(2) {
    [0] => string(3) "gir"
    [1] => string(3) "irk"
  }
}

EXPECT;

$array = array(
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => array(
        'gir', 'irk'
    )
);

$assert->setLabel('array as text');
$assert->same($var->dump($array), $expectArray);

// dump a string
$string = 'foo < bar > baz " dib & zim ? gir';
$expectString = 'string(33) "foo < bar > baz " dib & zim ? gir"' . "\n";
$assert->setLabel('string as text');
$assert->same($var->dump($string), $expectString);


// HTML
$var = Solar::factory('Solar_Debug_Var', array('output' => 'html'));

// object
$expectObject = '<pre>' . htmlspecialchars($expectObject) . '</pre>';
$assert->setLabel('object as html');
$assert->same($var->dump($object), $expectObject);

// array
$expectArray = '<pre>' . htmlspecialchars($expectArray) . '</pre>';
$assert->setLabel('array as html');
$assert->same($var->dump($array), $expectArray);

// string
$expectString = '<pre>' . htmlspecialchars($expectString) . '</pre>';
$assert->setLabel('string as html');
$assert->same($var->dump($string), $expectString);


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
