<?php

/**
* 
* Application index file.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Application index file.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

require_once 'Solar.php';
Solar::start();
$app = Solar::object('Solar_App_Bugs');
echo $app->output();
Solar::stop();
?>