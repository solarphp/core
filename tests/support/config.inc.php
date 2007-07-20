<?php
$config = array();

$config['Test']['include_path'] = dirname(dirname(__FILE__));

$config['Solar']['ini_set']['error_reporting'] = E_ALL | E_STRICT;
$config['Solar']['ini_set']['display_errors'] = true;
$config['Solar']['ini_set']['date.timezone'] = 'America/Chicago';

$config['Solar_Test_Example'] = array(
    'zim' => 'gaz',
);

$config['Solar_Debug_Var']['output'] = 'text';

$config['Solar_Sql_Adapter_Sqlite'] = array(
    'name' => ':memory:',
);

$config['Solar_Sql_Adapter_Mysql'] = array(
    'name'   => 'test',
    'user'   => null,
    'pass'   => null,
    'host'   => '127.0.0.1',
);

$config['Solar_Auth_Adapter_TypeKey']['token'] = 'foobarbaz';


return $config;
