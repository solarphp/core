<?php

/**
* 
* Default controller; for viewing bookmarks by tag intersection.
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
* Prepend for all controllers.
*/
include $this->helper('prepend');

// the requested user_id
$user_id = null;

// what tags are we looking for?
$tags = trim(Solar::pathinfo(1));

// the requested ordering of list results
$order = $this->getOrder();

// what page-number of the results are we looking for?
// the same, regardless of RSS or HTML.
$page = Solar::get('page', 1);

// get the list of results
if (! $tags) {
	// no tags requested, fetch everything
	$this->view->list = $bookmarks->fetchList(null, $order, $page);
} else {
	// some tags requested
	$this->view->list = $bookmarks->withTags($tags, $user_id, $order, $page);
};

// assign everything else
$this->view->rss['avail'] = true;
$this->view->count = $bookmarks->count;
$this->view->pages = $bookmarks->pages;
$this->view->page = $page;
$this->view->user_id = null; // requested user_id
$this->view->tags = $tags; // requested tags
$this->view->user_tags = null; // all tags for this user

// RSS or HTML?
$rss = Solar::get('rss', false);

if ($rss) {
	return $this->view('rss');
} else {
	return $this->view('list');
}

?>