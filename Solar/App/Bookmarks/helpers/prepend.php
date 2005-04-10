<?php

/**
* 
* Sets up the environment for all controller action scripts.
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
* 
* Sets up the environment for all controller action scripts.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
*/

// get the shared user object
$user = Solar::shared('user');

// get the shared template object and add the path for local templates
$tpl = Solar::shared('template');
$tpl->addPath('template', $this->dir['views']);

// add any additional template paths (for theming)
$tpl->addPath(
	'template',
	Solar::config('Solar_App_Bookmarks', 'template_path', '')
);

// RSS data for the page
$tpl->rss = array(
	'avail' => false,
	'title' => Solar::super('server', 'PATH_INFO'),
	'descr' => 'Solar_App_Bookmarks',
	'date'  => date('c'), // should be latest mod date in the $tpl->list
	'link' => Solar::super('server', 'REQUEST_URI'),
);

// get standalone objects
$bookmarks = Solar::object('Solar_Cell_Bookmarks');
$form = Solar::object('Solar_Form');

?>