<?php
// what operation are we performing?
$op = Solar::get('op', 'open');

// operations
switch (strtolower($op)) {

case 'list':
	$tpl->list = $bugs->fetchList();
	break;
	
case 'open':
default:
	$tpl->list = $bugs->fetchOpen();
	break;
}

// display
$tpl->setTemplate('list.tpl.php');
echo $tpl;
?>