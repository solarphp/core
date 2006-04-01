--TEST--
Solar_Base::locale()
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

$example = Solar::factory('Solar_Test_Example');

// English
Solar::setLocale('en_US');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'hello world'
);

// Italian
Solar::setLocale('it_IT');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'ciao mondo'
);

// Espa–ol
Solar::setLocale('es_ES');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'hola mundo'
);

// Language code not available, shows key instead of string.
Solar::setLocale('xx_XX');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'HELLO_WORLD'
);

// Language code available, but key not in class translations.
// Falls back to Solar-wide translations.
Solar::setLocale('en_US');
$assert->same(
    $example->locale('SUCCESS_FORM'),
    'Saved.'
);

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
