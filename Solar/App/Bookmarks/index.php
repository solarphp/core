<?php

/**
* 
* Application index file.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Start, echo the output from the application object, and stop.
*/
require_once 'Solar.php';
Solar::start();
$app = Solar::object('Solar_App_Bookmarks');
echo $app->output();
Solar::stop();
?>