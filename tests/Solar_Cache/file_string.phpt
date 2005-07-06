--TEST--
cache: file, string
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

$id = "coyote";

// make X passes agains the cache; it should hit and miss
for ($i = 0; $i < $loops; $i++) {
	
	// fetch against the cache
	$data = $cache->fetch($id);
	
	// did we get output from the cache?
	if (! $data) {
		// no, it was a miss
		$status = 'miss';
		$data = "My name is Wile E. Coyote.";
		$result = $cache->replace($id, $data);
	} else {
		$result = null;
		$status = 'hit';
	}
	
	// dump the results of this iteration
	Solar::dump($status, (string) $i);
	Solar::dump($data);
	flush();
	
	// wait a while before the next run
	sleep($sleep);
}

Solar::stop();
?>
--EXPECT--
0 string(4) "miss"
string(26) "My name is Wile E. Coyote."
1 string(3) "hit"
string(26) "My name is Wile E. Coyote."
2 string(3) "hit"
string(26) "My name is Wile E. Coyote."
3 string(4) "miss"
string(26) "My name is Wile E. Coyote."
4 string(3) "hit"
string(26) "My name is Wile E. Coyote."