<?php

// load locale strings for the application
Solar::$shared->locale->load('Solar_App_Bugs', 'Solar/App/Bugs/helper/locale/');

// get the shared user object
$user = Solar::shared('user');

// get the shared template object and add the path for Bugs templates
// (defaults to 'Solar/App/Bugs/view/')
$tpl = Solar::shared('template');
$tpl->addPath('template', dirname(__FILE__) . '/view/');

// add any additional template paths (for theming)
$tpl->addPath(
	'template',
	Solar::config('Solar_App_Bugs', 'template_path', '')
);

// get standalone objects for the bug-tracking table, the comments table,
// and a form builder
$bugs = Solar::object('Solar_Cell_Bugs');
$talk = Solar::object('Solar_Cell_Talk');
$form = Solar::object('Solar_Form');

// pick the right action script
switch(strtolower(Solar::get('action'))) {

// view one bug report
case 'item':
	include 'Solar/App/Bugs/controller/item.php';
	break;

// edit a bug report
case 'edit':
	include 'Solar/App/Bugs/controller/edit.php';
	break;

// view a list of bug reports (all, or only open)
case 'list_all':
case 'list_open':
default:
	include 'Solar/App/Bugs/controller/list.php';
	break;
}

// done!
?>