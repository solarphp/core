<?php
// prepend for all controllers
include $this->helper('prepend');

$action = Solar::get('action');

// operations
switch (strtolower($action)) {

// list all bugs regardless of open or closed
case 'list_all':
	$tpl->list = $bugs->fetchList();
	break;
	
// list only open bugs
case 'list_open':
default:
	$tpl->list = $bugs->fetchOpen();
	break;
}

// display
return $tpl->fetch('list.php');
?>