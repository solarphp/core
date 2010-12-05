<?php
$config = array();

/**
 * General ini settings.
 */
 
$config['Solar']['system'] = dirname(dirname(dirname(dirname(__FILE__))));

$config['Solar']['ini_set'] = array(
	'error_reporting' => (E_ALL|E_STRICT),
	'display_errors'  => true,
	'html_errors'     => false,
	'date.timezone'   => 'America/Chicago',
);

$config['Solar']['registry_set']['session_manager'] = 'Solar_Session_Manager';

/**
 * Database connections.
 */

$config['Solar_Sql'] = array(
	'adapter' => 'Solar_Sql_Adapter_Sqlite',
);

$config['Solar_Sql_Adapter_Mysql'] = array(
	'host'   => '127.0.0.1',
	'name'   => 'test',
);

$config['Solar_Sql_Adapter_Pgsql'] = array(
	'host'   => '127.0.0.1',
	'name'   => 'test',
	'user'   => 'postgres',
	'pass'   => 'postgres',
);

$config['Solar_Sql_Adapter_Sqlite'] = array(
    'name' => ':memory:',
);

return $config;
