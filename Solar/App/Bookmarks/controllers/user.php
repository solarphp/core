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
* 
* Controller for viewing bookmarks by user (and optionally by tag).
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
$user_id = Solar::pathinfo(1);

// what tags are we looking for?
$tags = Solar::pathinfo(2);

// the requested ordering of list results
$order = $this->getOrder();

// RSS or HTML? set up the page number accordingly (by default, RSS gets
// all bookmarks, not just one page).
$rss = Solar::get('rss', false);
if ($rss) {
	$tpl->setTemplate('rss.php');
	$page = Solar::get('page', 0);
} else {
	$tpl->setTemplate('list.php');
	$page = Solar::get('page', 1);
}

// get the list of entries
if ($tags) {
	$tpl->list = $bookmarks->withTags($tags, $user_id, $order, $page);
} else {
	$tpl->list = $bookmarks->forUser($user_id, $order, $page);
}

// assign, and done!
$tpl->rss['avail'] = true;
$tpl->count = $bookmarks->count;
$tpl->pages = $bookmarks->pages;
$tpl->page = $page;
$tpl->user_id = $user_id; // requested user_id
$tpl->tags = $tags; // requested tags
$tpl->user_tags = $bookmarks->userTags($user_id); // all tags for this user

return $tpl->fetch();
?>