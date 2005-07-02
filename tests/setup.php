<?php
function dump(&$var, $label = null) 
{
	Solar::dump($var, $label);
}

define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/config.php');
require_once 'Solar.php';
?>