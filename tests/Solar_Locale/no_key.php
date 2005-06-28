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
	dump($result);
	
	// only the en_US file has the ENGLISH_ONLY key
	$result = $example->locale('ENGLISH_ONLY');
	dump($result);
}

?>
