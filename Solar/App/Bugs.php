<?php

/**
* 
* Bug-tracker application.
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
* Application controller class.
*/

require_once 'Solar/App.php';


/**
* 
* Bug-tracker application.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

class Solar_App_Bugs extends Solar_App {
	protected $default_controller = 'listOpen';
}
?>