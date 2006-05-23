<?php
// assume Solar is on the include_path.
include_once 'Solar.php';

if (! class_exists('Solar')) {
    // assume Solar is at $target/tests/.
    // this is good for SVN checkouts
    // and the as-delivered tarball/pearball.
    include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Solar.php';
}

if (! class_exists('Solar')) {
    // assume Solar is at $target/tests/Solar/tests/.
    // this is good for when we're already pear-installed.
    include_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'Solar.php';
}

if (! class_exists('Solar')) {
    // not in any of the known configurations.
    throw Exception('Cannot find Solar.php.');
}

// proceed with testing
$config = dirname(__FILE__) . '/config.inc.php';
Solar::start($config);

// configure and instantiate the suite
$config = array(
    'dir' => dirname(__FILE__) . '/Test/',
);
$suite = Solar::factory('Solar_Test_Suite', $config);

// run the test series
$series = isset($argv[1]) ? trim($argv[1]) : null;
$suite->run($series);

// done
Solar::stop();
?>