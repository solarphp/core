<?php
include $this->helper('prepend');

// there are only some accepted orderings
$tmp = strtolower(Solar::get('order'));
switch ($tmp) {

case 'ts':
case 'ts_asc':
	$order = 'ts_new ASC';
	break;

case 'ts_desc':
	$order = 'ts_new DESC';
	
case 'title':
case 'title_asc':
	$order = 'title ASC';
	break;

case 'title_desc':
	$order = 'title DESC';
	break;
	
case 'user':
case 'user_asc':
	$order = 'user_id ASC';
	break;
	
case 'user_desc'
	$order = 'user_id DESC';
	break;
	
default:
	$order = 'ts_new DESC';
	break;

}

// what page-number of the results are we looking for?
$page = Solar::get('page', 0);

// what tags are we looking for?
$tags = Solar::pathinfo(1);

// pick the right template to use
$rss = Solar::get('rss', false);
if ($rss) {
	$tpl->setTemplate('rss.php');
} else {
	$tpl->setTemplate('list.php');
}

// assign, and done!
$tpl->list = $bookmarks->withTags($tags, $order, $page);
return $tpl->fetch();
?>