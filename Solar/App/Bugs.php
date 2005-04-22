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
	protected function setup()
	{
		// controller action source and default
		$this->action['src']     = 'get';
		$this->action['var']     = 'action';
		$this->action['default'] = 'listOpen';
		
		// the list of users who are allowed admin privs
		$this->config['admin_user'] = array();
		
		// the list of roles that are allowed admin privs
		$this->config['admin_role'] = array();
	}
}
?>