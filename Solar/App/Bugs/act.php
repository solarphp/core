<?php
// get the shared user object
$user = Solar::shared('user');

// get the shared template object and add the path
// for Bugs templates (defaults to 'Solar/App/Bugs/tpl/')
$tpl = Solar::shared('template');
$tpl->addPath(
	'template',
	Solar::config('Solar_App_Bugs', 'template_path', 'Solar/App/Bugs/tpl/')
);

// get standalone objects for the bug-tracking table, the comments table,
// and a form builder
$bugs = Solar::object('Solar_Cell_Bugs');
$talk = Solar::object('Solar_Cell_Talk');
$form = Solar::object('Solar_Form');

// pick the right action script
switch(strtolower(Solar::get('action'))) {

case 'view':
	include 'Solar/App/Bugs/act/view.php';
	break;

case 'edit':
	include 'Solar/App/Bugs/act/edit.php';
	break;
	
case 'list':
default:
	include 'Solar/App/Bugs/act/list.php';
	break;
}

// done!
?>