--TEST--
Solar_Exception_* (all specific exception classes, and their translations)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

// list of error codes
$list = array(
    'ERR_CONNECTION_FAILED'      => 'ConnectionFailed',
    'ERR_EXTENSION_NOT_LOADED'   => 'ExtensionNotLoaded',
    'ERR_FILE_NOT_FOUND'         => 'FileNotFound',
    'ERR_FILE_NOT_READABLE'      => 'FileNotReadable',
    'ERR_METHOD_NOT_CALLABLE'    => 'MethodNotCallable',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'MethodNotImplemented',
);

$example = Solar::factory('Solar_Test_Example');

foreach ($list as $code => $name) {
    try {
        // throw a Solar-wide specific exception based on an error code string
        $example->exceptionFromCode($code);
    } catch (Exception $e) {
        $assert->isInstance($e, "Solar_Exception_$name");
        // make sure the class and code works
        $assert->same($e->getClass(), 'Solar_Test_Example');
        $assert->same($e->getCode(), $code);
        // make sure the automatic translation works
        $assert->same($e->getMessage(), $example->locale($code));
    }
}

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
