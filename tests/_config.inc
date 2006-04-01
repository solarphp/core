<?php
$config = array();

$config['Solar']['ini_set']['error_reporting'] = E_ALL | E_STRICT;
$config['Solar']['ini_set']['display_errors'] = true;
$config['Solar']['ini_set']['date.timezone'] = 'America/Chicago';

$config['Solar_Test_Example'] = array(
	'zim' => 'gaz',
);

$config['Solar_Debug_Var']['output'] = 'text';

return $config;
?>