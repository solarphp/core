<?php

/**
* 
* Social bookmarking application.
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
* @version $Id: Bugs.php 110 2005-03-28 17:18:39Z pmjones $
* 
*/


/**
* Application controller class.
*/

require_once 'Solar/App.php';


/**
* 
* Social bookmarking application.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
*/

class Solar_App_Bugs extends Solar_App {
	protected $action_src = 'pathinfo';
	protected $action_var = 0;
	protected $action_default = 'tag';
}
?>