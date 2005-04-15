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
* 
* Default controller; for viewing bookmarks by tag intersection.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
*/



// helper for all controllers
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
	$tpl->list = $bookmarks->fetchList(null, $order, $page);
} else {
	// some tags requested
	$tpl->list = $bookmarks->withTags($tags, $user_id, $order, $page);
};

// RSS or HTML?
$rss = Solar::get('rss', false);
if ($rss) {
	$tpl->setTemplate('rss.php');
} else {
	$tpl->setTemplate('list.php');
}

// assign everything else and display
$tpl->rss['avail'] = true;
$tpl->count = $bookmarks->count;
$tpl->pages = $bookmarks->pages;
$tpl->page = $page;
$tpl->user_id = null; // requested user_id
$tpl->tags = $tags; // requested tags
$tpl->user_tags = null; // all tags for this user
return $tpl->fetch();
?>