--TEST--
Solar_Debug_Timer (all tests)
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

// does the class create the locale config?
$timer = Solar::factory('Solar_Debug_Timer', array('output' => 'text'));


$mark['__start'] = microtime(true);
$timer->start();
for ($i = 0; $i < 4; $i++) {
    $wait = rand(1,2);
    sleep($wait);
    $mark[$i] = microtime(true);
    $timer->mark($i);
}
$mark['__stop'] = microtime(true);
$timer->stop();

// get the timer profile
$profile = $timer->profile();

// make sure we hit all the marks
$assert->isTrue(count($profile) == count($mark));

foreach ($profile as $val) {
    // make sure the profiled times are near the
    // times we marked
    $key = $val['name'];
    $diff = abs($val['time'] - $mark[$key]);
    $assert->setLabel("'$key': {$val['time']} near {$mark[$key]} diff $diff");
    $assert->isTrue($diff <= 0.0001);
}

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
