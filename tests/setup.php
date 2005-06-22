<?php
function dump(&$var, $label = null) 
{
	if ($label) {
		echo $label . " ";
	}
	ob_start();
	var_dump($var);
	$output = ob_get_clean();
	$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
	echo $output;
}

define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/config.php');
require_once 'Solar.php';
?>
