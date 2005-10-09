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

// the requested owner_handle
$owner_handle = Solar::pathinfo(1);

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

// get the list of results
$this->view->list = $bookmarks->fetchList($owner_handle, $tags, $order, $page);

// get the total pages and row-count
$total = $bookmarks->fetchCount($owner_handle, $tags);

// assign everything else
$this->view->rss['avail'] = true;
$this->view->count        = $total['count'];
$this->view->pages        = $total['pages'];
$this->view->order        = $order;
$this->view->page         = $page;
$this->view->owner_handle = $owner_handle; // requested owner_handle
$this->view->tags         = $tags; // the requested tags
$this->view->tags_in_use  = $bookmarks->fetchTagList($owner_handle); // all tags for this user

// return the view output
return $this->view($viewname);
?>