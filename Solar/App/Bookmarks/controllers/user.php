<?php

/**
* 
* Controller for viewing bookmarks by user (and optionally by tag).
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
$user_id = Solar::pathinfo(1);

// what tags are we looking for?
$tags = Solar::pathinfo(2);

// the requested ordering of list results
$order = $this->getOrder();

// RSS or HTML? set up the page number accordingly (by default, RSS gets
// all bookmarks, not just one page).
$rss = Solar::get('rss', false);
if ($rss) {
	$viewname = 'rss';
	$page = Solar::get('page', 0);
} else {
	$viewname = 'list';
	$page = Solar::get('page', 1);
}

// get the list of entries
if ($tags) {
	$this->view->list = $bookmarks->withTags($tags, $user_id, $order, $page);
} else {
	$this->view->list = $bookmarks->forUser($user_id, $order, $page);
}

// assign, and done!
$this->view->rss['avail'] = true;
$this->view->count = $bookmarks->count;
$this->view->pages = $bookmarks->pages;
$this->view->page = $page;
$this->view->user_id = $user_id; // requested user_id
$this->view->tags = $tags; // requested tags
$this->view->user_tags = $bookmarks->userTags($user_id); // all tags for this user

return $this->view($viewname);
?>