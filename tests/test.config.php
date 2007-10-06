<?php
$config = array();

/**
 * General ini settings.
 */
$config['Solar']['ini_set'] = array(
	'error_reporting' => (E_ALL|E_STRICT),
	'display_errors'  => true,
	'html_errors'     => false,
	'date.timezone'   => 'America/Chicago',
);

/**
 * Database connections.
 */

$config['Solar_Sql'] = array(
	'adapter' => 'Solar_Sql_Adapter_Mysql',
);

$config['Solar_Sql_Adapter_Mysql'] = array(
	'host'   => '127.0.0.1',
	'name'   => 'test',
);

$config['Solar_Sql_Adapter_Pgsql'] = array(
	'host'   => '127.0.0.1',
	'name'   => 'test',
);

$config['Solar_Sql_Adapter_Sqlite'] = array(
    'name' => ':memory:',
);

return $config;
