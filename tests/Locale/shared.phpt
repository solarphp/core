--TEST--
locale: shared Solar('locale') calls
--FILE---
<?php
require_once '../setup.php';
Solar::start();

// the locale directory
$dir = dirname(__FILE__) . '/locale/';

// output the current code
$result = Solar::shared('locale')->code();
Solar::dump($result);

// load the testing locale and try the default translation
Solar::shared('locale')->load('testing', $dir);
$result = Solar::shared('locale')->string('testing', 'HELLO_WORLD');
Solar::dump($result);

// Italiano
Solar::shared('locale')->setCode('it_IT');
$result = Solar::shared('locale')->code();
Solar::dump($result);
Solar::shared('locale')->load('testing', $dir);
$result = Solar::shared('locale')->string('testing', 'HELLO_WORLD');
Solar::dump($result);

// Espa–ol
Solar::shared('locale')->setCode('es_ES');
$result = Solar::shared('locale')->code();
Solar::dump($result);
Solar::shared('locale')->load('testing', $dir);
$result = Solar::shared('locale')->string('testing', 'HELLO_WORLD');
Solar::dump($result);

?>
--EXPECT--
string(5) "en_US"
string(11) "hello world"
string(5) "it_IT"
string(10) "ciao mondo"
string(5) "es_ES"
string(10) "hola mundo"