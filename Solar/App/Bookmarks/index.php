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
* @version $Id: index.php 113 2005-03-28 17:54:33Z pmjones $
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
* @subpackage Solar_App_Bookmarks
* 
*/

require_once 'Solar.php';
Solar::start();
$app = Solar::object('Solar_App_Bookmarks');
echo $app->output();
Solar::stop();
?>