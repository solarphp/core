<?php

/**
* 
* Controller action script for viewing a list of all reports (open and closed).
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
* Controller action script for viewing a list of all reports (open and closed).
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

// prepend for all controllers
include $this->helper('prepend');

// list all bugs regardless of open or closed
$this->view->list = $bugs->fetchList();

// display
return $this->view('list');
?>