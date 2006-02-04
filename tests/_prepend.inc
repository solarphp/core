<?php
ini_set('error_reporting', E_ALL & E_STRICT);
ini_set('display_errors', true);

define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/_config.inc');
require_once 'Solar.php';
Solar::start();

require_once 'Solar/Test/Assert.php';
$assert = new Solar_Test_Assert();
?>