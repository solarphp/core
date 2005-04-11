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

// get the shared template object and add the path for Bugs templates
$tpl = Solar::shared('template');
$tpl->addPath('template', $this->dir['views']);

// add any additional template paths (for theming)
$tpl->addPath(
	'template',
	Solar::config('Solar_App_Bugs', 'template_path', '')
);

// get standalone objects for the bug-tracking table, the comments table,
// and a form builder
$bugs = Solar::object('Solar_Cell_Bugs');
$comments = Solar::object('Solar_Cell_Comments');
$form = Solar::object('Solar_Form');

?>