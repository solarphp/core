--TEST--
locale: Solar_Base::locale() calls
--FILE---
<?php
require_once '../setup.php';
Solar::start();

class Example extends Solar_Base {
	public function __construct($config = null)
	{
		$this->config['locale'] = dirname(__FILE__) . '/locale/';
		parent::__construct($config);
	}
}

$example = new Example();

// English
$result = Solar::shared('locale')->code();
dump($result);
$result = $example->locale('HELLO_WORLD');
dump($result);

// Italiano
Solar::shared('locale')->setCode('it_IT');
$result = Solar::shared('locale')->code();
dump($result);
$result = $example->locale('HELLO_WORLD');
dump($result);

// Espa–ol
Solar::shared('locale')->setCode('es_ES');
$result = Solar::shared('locale')->code();
dump($result);
$result = $example->locale('HELLO_WORLD');
dump($result);

?>
--EXPECT--
string(5) "en_US"
string(11) "hello world"
string(5) "it_IT"
string(10) "ciao mondo"
string(5) "es_ES"
string(10) "hola mundo"