<?php

/**
* 
* Sets up the environment for all controller action scripts.
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
* Sets up the environment for all controller action scripts.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

// get the shared user object
$user = Solar::shared('user');

// get standalone objects for the bug-tracking table, the comments table,
// and a form builder
$bugs = Solar::object('Solar_Cell_Bugs');
$comments = Solar::object('Solar_Cell_Comments');
$form = Solar::object('Solar_Form');

?>