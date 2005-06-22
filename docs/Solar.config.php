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

$config = array();


/**
* Document root.
*/

$docroot = $_SERVER['DOCUMENT_ROOT'];


/**
* General ini settings.
*/

$config['Solar']['ini_set'] = array(
	'error_reporting' => (E_ALL|E_STRICT),
	'display_errors'  => true,
	'html_errors'     => true
);


/**
* Default database connection.
*/

$config['Solar_Sql'] = array(
	'class' => 'Solar_Sql_Driver_Sqlite',
	'file'  => "$docroot/solar.sqlite",
	'mode'  => '0666',
);


/**
* User authentication source.
*/

$config['Solar_User_Auth'] = array(
	'class'    => 'Solar_User_Auth_Htpasswd',
	'options'  => array(
		'file' => "$docroot/htpasswd.conf",
	),
);


/**
* Done!
*/

return $config;
?>