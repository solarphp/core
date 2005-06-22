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

// get the shared user object
$user = Solar::shared('user');

// RSS data for the page (regardless of whether it's actually available)
$link = Solar::object('Solar_Uri');
$link->query('set', 'rss', '1');

$this->view->rss = array(
	'avail' => false,
	'title' => Solar::server('PATH_INFO'),
	'descr' => 'Solar_App_Bookmarks',
	'date'  => date('c'), // should be latest mod date in the $this->view->list
	'link'  => $link->export(),
);

unset($link);

// get standalone objects
$bookmarks = Solar::object('Solar_Cell_Bookmarks');
$form = Solar::object('Solar_Form');

// allow user to set the "count" for each page
$bookmarks->setRowsPerPage(Solar::get('rows_per_page', 10));
?>