--TEST--
cache: file, object
--FILE---
<?php
require_once '../setup.php';
Solar::start();

$life  = 7;
$sleep = 3;
$loops = 5;

$config = array(
	'class' => 'Solar_Cache_File',
	'options' => array(
		'path' => '/tmp/Solar_Cache_Testing',
		'life' => $life,
	)
);

$cache = Solar::object('Solar_Cache', $config);
$cache->deleteAll();

class Coyote extends Solar_Base {
	public $acme = 'Acme Industries, LLC';
	protected $meal = 'Roadrunner';
}

$id = "coyote_object";

// make X passes agains the cache; it should hit and miss
for ($i = 0; $i < $loops; $i++) {
	
	// fetch against the cache
	$data = $cache->fetch($id);
	
	// did we get output from the cache?
	if (! $data) {
		// no, it was a miss
		$status = 'miss';
		$data = new Coyote();
		$result = $cache->replace($id, $data);
	} else {
		$result = null;
		$status = 'hit';
	}
	
	// dump the results of this iteration
	dump($status, (string) $i);
	dump($data);
	flush();
	
	// wait a while before the next run
	sleep($sleep);
}

Solar::stop();
?>
--EXPECT--
string(4) "miss"
object(Coyote)#11 (3) {
  ["acme"] => string(20) "Acme Industries, LLC"
  ["meal:protected"] => string(10) "Roadrunner"
  ["config:protected"] => array(0) {
  }
}
1 string(3) "hit"
object(Coyote)#12 (3) {
  ["acme"] => string(20) "Acme Industries, LLC"
  ["meal:protected"] => string(10) "Roadrunner"
  ["config:protected"] => array(0) {
  }
}
2 string(3) "hit"
object(Coyote)#11 (3) {
  ["acme"] => string(20) "Acme Industries, LLC"
  ["meal:protected"] => string(10) "Roadrunner"
  ["config:protected"] => array(0) {
  }
}
3 string(4) "miss"
object(Coyote)#11 (3) {
  ["acme"] => string(20) "Acme Industries, LLC"
  ["meal:protected"] => string(10) "Roadrunner"
  ["config:protected"] => array(0) {
  }
}
4 string(3) "hit"
object(Coyote)#12 (3) {
  ["acme"] => string(20) "Acme Industries, LLC"
  ["meal:protected"] => string(10) "Roadrunner"
  ["config:protected"] => array(0) {
  }
}