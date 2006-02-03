--TEST--
Solar_Base::locale()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$example = Solar::factory('Solar_Test_Example');

// English
Solar::shared('locale')->reset('en_US');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'hello world'
);

// Italian
Solar::shared('locale')->reset('it_IT');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'ciao mondo'
);

// Espa–ol
Solar::shared('locale')->reset('es_ES');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'hola mundo'
);

// Language code not available, shows key instead of string.
Solar::shared('locale')->reset('xx_XX');
$assert->same(
    $example->locale('HELLO_WORLD'),
    'HELLO_WORLD'
);

// Language code available, but key not in class translations.
// Falls back to Solar-wide translations.
Solar::shared('locale')->reset('en_US');
$assert->same(
    $example->locale('OK_SAVED'),
    'Saved.'
);

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
