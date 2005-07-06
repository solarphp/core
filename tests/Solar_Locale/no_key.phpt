--TEST--
locale: no translation keys available
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
	
	// only the en_US file has the ENGLISH_ONLY key
	$result = $example->locale('ENGLISH_ONLY');
	Solar::dump($result);
}

?>
--EXPECT--
string(5) "en_US"
string(25) "key is only in en_US file"
string(5) "it_IT"
string(12) "ENGLISH_ONLY"
string(5) "es_ES"
string(12) "ENGLISH_ONLY"