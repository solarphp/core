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

// the requested owner_handle (none)
$owner_handle = null;

// what tags are we looking for?
$tags = trim(Solar::pathinfo(1));

// the requested ordering of list results
$order = $this->getOrder();

// what page-number of the results are we looking for?
// (regardless of RSS or HTML)
$page = Solar::get('page', 1);

// get the list of results
$this->view->list = $bookmarks->fetchList($owner_handle, $tags, $order, $page);

// get the total pages and row-count
$total = $bookmarks->countPages($owner_handle, $tags);

// assign everything else
$this->view->rss['avail'] = true;
$this->view->count        = $total['count'];
$this->view->pages        = $total['pages'];
$this->view->order        = $order;
$this->view->page         = $page;
$this->view->owner_handle = null; // requested owner_handle
$this->view->tags         = $tags; // the requested tags
$this->view->tags_in_use  = $bookmarks->fetchTagList($owner_handle); // all tags

// RSS or HTML?
$rss = Solar::get('rss', false);

if ($rss) {
    return $this->view('rss');
} else {
    return $this->view('list');
}

?>