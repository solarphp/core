<?php

/**
* 
* Sample configuration file for Solar.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Empty base array.
*/

$conf = array();


/**
* General ini settings.
*/

$config['Solar']['ini_set'] = array(
	'error_reporting' => (E_ALL|E_STRICT),
	'display_errors' => true,
	'html_errors' => true
);


/**
* Shared objects to load as called.
*/

$config['Solar']['shared'] = array(
	'cache' => 'Solar_Cache', // uses default config
	'sql'   => 'Solar_Sql', // uses default config
	'user'  => 'Solar_User' // uses default config
	/*
	'My_Sql_Object'   => array( // uses custom config
		'Solar_Sql',
		array(...) // these are the custom configs
	),
	*/
);

/**
* Always load these shared objects.
*/
$config['Solar']['autoshare'] = array(
	'sql',
	'user'
);


/**
* Default database connection.
*/

$config['Solar_Sql'] = array(
	'class' => 'Solar_Sql_Driver_Mysql',
	'host'  => 'localhost',
	'user'  => 'somebody',
	'pass'  => '********',
	'name'  => 'database'
);


/**
* Full user config, with authentication source and group/role capture.
*/

$config['Solar_User'] = array(
	'auth' => array(
		'class' => 'Solar_User_Auth_Ldap',
		'options' => array(
			'url' => 'ldap://ds4.memphis.edu/',
			'format' => 'uid=%s,ou=people,o=the university of memphis,st=tn,c=us'
		),
	),
	'role' => array(
		'refresh' => false,
		'class' => 'Solar_User_Role_Multi',
		'options' => array(
			0 => array(
				'Solar_User_Role_Ldap',
				array(
					'url'    => 'ldap://ds4.memphis.edu/',
					'basedn' => 'ou=people,o=the university of memphis,st=tn,c=us',
					'filter' => 'uid=%s',
					'attrib' => array('ou', 'umemphisaffiliation'),
					'binddn' => 'uid=svcdn-myclass, ou=Special Users, o=The University of Memphis, st=TN, c=us',
					'bindpw' => '********'
				),
			),
			1 => array(
				'Solar_User_Role_File',
				array(
					'file' => '/var/www/conf/groups.txt'
				),
			),
		),
	),
);


/**
* Done!
*/

return $config;
?>