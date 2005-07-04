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
	
	// looking at the RSS view.
	$viewname = 'rss';
	if ($tags) {
		// if tags are requested, get all pages by default.
		$page = Solar::get('page', 0);
	} else {
		// otherwise it's all bookmarks for the user, get only page 1 by default.
		$page = Solar::get('page', 1);
	}
	
} else {

	$viewname = 'list';
	$page = Solar::get('page', 1);
	
	// make sure the RSS link is available in the template
	$this->view->rss['avail'] = true;
	
	if ($tags) {
		// there are tags requested, so the RSS should show all pages
		// and ignore the rows-per-page settings.  build a custom
		// RSS link for this.
		$link = Solar::object('Solar_Uri');
		$link->setQuery('rss', '1');
		$link->clearQuery('page');
		$link->clearQuery('rows_per_page');
		$this->view->rss['link'] = $link->export();
		unset($link);
	}
}

// get the list of entries and assign to template
if ($tags) {
	$this->view->list = $bookmarks->withTags($tags, $user_id, $order, $page);
} else {
	$this->view->list = $bookmarks->forUser($user_id, $order, $page);
}

// assign remaining variables
$this->view->count     = $bookmarks->count;
$this->view->pages     = $bookmarks->pages;
$this->view->page      = $page;
$this->view->user_id   = $user_id; // requested user_id
$this->view->tags      = $tags; // requested tags
$this->view->user_tags = $bookmarks->userTags($user_id); // all tags for this user


// return the view output
return $this->view($viewname);
?>