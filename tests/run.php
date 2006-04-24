<?php
define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/config.inc.php');

require 'Solar.php';
Solar::start();

// configure and run the suite
$config = array(
    'dir' => dirname(__FILE__) . '/Test',
    'sub' => isset($argv[1]) ? trim($argv[1]) : '',
);
$suite = Solar::factory('Solar_Test_Suite', $config);
$suite->run();

// done
Solar::stop();
?>