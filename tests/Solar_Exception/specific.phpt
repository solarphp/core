--TEST--
Solar_Exception_* (all specific exception classes, and their translations)
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
