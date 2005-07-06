--TEST--
locale: singular/plural
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

// the codes we will try:
$codes = array('en_US', 'it_IT', 'es_ES');

foreach ($codes as $code) {
	
	// set the code
	Solar::shared('locale')->setCode($code);
	$result = Solar::shared('locale')->code();
	Solar::dump($result);
	
	// try 0, 1, and 2
	for ($i = 0; $i < 3; $i++) {
		$result = $i . ' ' . $example->locale('APPLE', $i);
		Solar::dump($result);
	}
}

?>
--EXPECT--
string(5) "en_US"
string(8) "0 apples"
string(7) "1 apple"
string(8) "2 apples"
string(5) "it_IT"
string(6) "0 mele"
string(6) "1 mela"
string(6) "2 mele"
string(5) "es_ES"
string(10) "0 manzanas"
string(9) "1 manzana"
string(10) "2 manzanas"