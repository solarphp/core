--TEST--
cache: file, array
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

$id = "coyote_array";

// make X passes agains the cache; it should hit and miss
for ($i = 0; $i < $loops; $i++) {
	
	// fetch against the cache
	$data = $cache->fetch($id);
	
	// did we get output from the cache?
	if (! $data) {
		// no, it was a miss
		$status = 'miss';
		$data = array(
			'name' => 'Wile E.',
			'type' => 'Coyote',
			'eats' => 'Roadrunner',
			'flag' => 'Not again!',
		);
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
array(4) {
  ["name"] => string(7) "Wile E."
  ["type"] => string(6) "Coyote"
  ["eats"] => string(10) "Roadrunner"
  ["flag"] => string(10) "Not again!"
}
1 string(3) "hit"
array(4) {
  ["name"] => string(7) "Wile E."
  ["type"] => string(6) "Coyote"
  ["eats"] => string(10) "Roadrunner"
  ["flag"] => string(10) "Not again!"
}
2 string(3) "hit"
array(4) {
  ["name"] => string(7) "Wile E."
  ["type"] => string(6) "Coyote"
  ["eats"] => string(10) "Roadrunner"
  ["flag"] => string(10) "Not again!"
}
3 string(4) "miss"
array(4) {
  ["name"] => string(7) "Wile E."
  ["type"] => string(6) "Coyote"
  ["eats"] => string(10) "Roadrunner"
  ["flag"] => string(10) "Not again!"
}
4 string(3) "hit"
array(4) {
  ["name"] => string(7) "Wile E."
  ["type"] => string(6) "Coyote"
  ["eats"] => string(10) "Roadrunner"
  ["flag"] => string(10) "Not again!"
}