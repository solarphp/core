<?php
require_once 'Solar.php';
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