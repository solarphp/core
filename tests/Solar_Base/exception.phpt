--TEST--
Solar_Base::_exception()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
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
$example2 = Solar::factory('Solar_Test_Example2');
try {
    $example2->solarGenericException();
    $assert->fail('Expected exception not thrown.');
} catch (Exception $e) {
    $assert->isInstance($e, 'Solar_Exception');
}


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete