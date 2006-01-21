--TEST--
base: $this->locale() fallback to generic Solar translations
--FILE---
<?php
require_once '../setup.php';
Solar::start();

class Example extends Solar_Base {
	public function __construct($config = null)
	{
		$this->_config['locale'] = dirname(__FILE__) . '/locale/';
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
	
	// none of the locale files has an OK_SAVED key.
	// en_US should fall back to the system-wide locale strings.
	// others will return just the key.
	$result = $example->locale('OK_SAVED');
	Solar::dump($result);
}

?>
--EXPECT--
string(5) "en_US"
string(6) "Saved."
string(5) "it_IT"
string(8) "OK_SAVED"
string(5) "es_ES"
string(8) "OK_SAVED"