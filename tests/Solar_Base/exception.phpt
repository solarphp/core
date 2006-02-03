--TEST--
Solar_Base::_exception()
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


// throw a specific exception for the class
$example = Solar::factory('Solar_Test_Example');
try {
    $example->classSpecificException();
    $assert->fail('Expected exception not thrown.');
} catch (Exception $e) {
    $assert->isInstance($e, 'Solar_Test_Example_Exception_CustomCondition');
}

// fall back to a specific exception for Solar as a whole
$example = Solar::factory('Solar_Test_Example');
try {
    $example->solarSpecificException();
    $assert->fail('Expected exception not thrown.');
} catch (Exception $e) {
    $assert->isInstance($e, 'Solar_Exception_FileNotFound');
}

// fall back to a generic exception for the class
$example = Solar::factory('Solar_Test_Example');
try {
    $example->classGenericException();
    $assert->fail('Expected exception not thrown.');
} catch (Exception $e) {
    $assert->isInstance($e, 'Solar_Test_Example_Exception');
}

// fall back to a generic exception for Solar as a whole.
try {
    $example->solarGenericException();
    $assert->fail('Expected exception not thrown.');
} catch (Exception $e) {
    $assert->isInstance($e, 'Solar_Exception');
}


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
