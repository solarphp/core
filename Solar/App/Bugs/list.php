<?php
// startup
require_once 'Solar.php';
Solar::start();

// get a shared template object
$tpl = Solar::shared('template');

// add the path to the local templates
$tpl->addPath('template', './tpl/');

// get a local Bugs object
$bugs = Solar::object('Solar_Cell_Bugs');

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
$tpl->setTemplate('index.tpl.php');
echo $tpl;

// shutdown
Solar::stop();
?>