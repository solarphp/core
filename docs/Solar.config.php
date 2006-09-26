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
* @license http://opensource.org/licenses/bsd-license.php BSD
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
	'adapter' => 'Solar_Sql_adapter_Sqlite',
	'config'  => array(
    	'name'   => "$docroot/solar.sqlite",
	)
);


/**
* User authentication source.
*/
$config['Solar_Auth'] = array(
	'adapter' => 'Solar_Auth_Adapter_Htpasswd',
	'config' => array(
		'file' => "$docroot/htpasswd.conf",
	),
);


/**
* Done!
*/
return $config;
?>