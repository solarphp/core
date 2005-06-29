--TEST--
base: config key overrides
--FILE---
<?php
require_once '../setup.php';
Solar::start();

class Example extends Solar_Base {
	
	protected $config = array(
		'opt_1' => 'foo',
		'opt_2' => 'bar',
		'opt_3' => 'baz'
	);
	
	public function testing()
	{
		return $this->config;
	}
}

$example = Solar::object('Example');
$result = $example->testing();
dump($result);

$opts = array('opt_3' => 'dib');
$example = Solar::object('Example', $opts);
$result = $example->testing();
dump($result);

// should be foo, gir, baz
// then foo, gir, dib
?>
--EXPECT--
array(3) {
  ["opt_1"] => string(3) "foo"
  ["opt_2"] => string(3) "gir"
  ["opt_3"] => string(3) "baz"
}
array(3) {
  ["opt_1"] => string(3) "foo"
  ["opt_2"] => string(3) "gir"
  ["opt_3"] => string(3) "dib"
}