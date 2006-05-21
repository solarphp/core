<?php
$config = array();

$config['Test']['include_path'] = '/usr/local/share/pear';

$config['Solar']['ini_set']['error_reporting'] = (E_ALL | E_STRICT);
$config['Solar']['ini_set']['display_errors'] = true;
$config['Solar']['ini_set']['date.timezone'] = 'America/Chicago';

$config['Solar_Test_Example'] = array(
	'zim' => 'gaz',
);

$config['Solar_Debug_Var']['output'] = 'text';

$config['Solar_Sql'] = array(
    'driver' => 'Solar_Sql_Driver_Sqlite',
    'name'   => '/tmp/solar_test_' . time() . '.sq3',
);

return $config;
?>