<?php
define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/_config.php');
require_once 'Solar.php';
Solar::start();

require_once 'Solar/Test/Assert.php';
$assert = new Solar_Test_Assert();
?>