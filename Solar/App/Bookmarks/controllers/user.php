<?php

/*
http://example.com/bookmarks.php/user/pmjones/tag+tag+tag?order=title&page=1&rss=0
*/

// helper for all controllers
include $this->helper('prepend');

// the requested user_id
$user_id = Solar::pathinfo(1);

// what tags are we looking for?
$tags = Solar::pathinfo(2);

// the requested ordering of list results
$order = $this->getOrder();

// what page-number of the results are we looking for?
$page = Solar::get('page', 0);

// RSS or HTML?
$rss = Solar::get('rss', false);
if ($rss) {
	$tpl->setTemplate('rss.php');
} else {
	$tpl->setTemplate('list.php');
}

// assign, and done!
$tpl->user_id = $user_id; // requested user_id
$tpl->tags = $tags; // requested tags
$tpl->user_tags = null; // all tags for this user
$tpl->list = $bookmarks->tags($tags, $user_id, $order, $page); // results

return $tpl->fetch();
?>