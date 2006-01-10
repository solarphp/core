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

// RSS link for the page (regardless of whether it's actually available)
$link = Solar::object('Solar_Uri');
$link->setQuery('rss', '1');

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

// allow uri to set the "count" for each page (default 10)
$bookmarks->paging(Solar::get('paging', 10));

// set the view locale to Solar_App_Bookmarks
$this->view->locale('Solar_App_Bookmarks::');
?>