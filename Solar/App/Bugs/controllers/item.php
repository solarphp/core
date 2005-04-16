<?php

/**
* 
* Controller action script for viewing a single bug report.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
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
* Controller action script for viewing a single bug report.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

// prepend for all controllers
include $this->helper('prepend');

// get the bug ID to view
$id = Solar::get('id');

// get the bug report data
$data = $bugs->fetchItem($id);

// does the report exist?
if (Solar::isError($data)) {
	$tpl->setTemplate('error.php');
	$tpl->error = $data;
	echo $tpl;
	exit;
}

// get the comment form elements and set defaults
// (put them in an array called 'comments')
$form->setElements($comments->formElements('mini'), 'comments');

// populate the form with submitted values
$form->populate();

// what operation are we performing?
$op = Solar::post('op');

// operations: Save Comment
if ($op == Solar::locale('Solar', 'OP_SAVE')) {
	
	if (! $form->validate()) {
	
		$form->feedback[] = Solar::locale('Solar', 'ERR_FORM');
		
	} else {
	
		// get the form values
		$values = $form->values();
		
		// add required elements
		$values['comments']['rel'] = 'sc_bugs';
		$values['comments']['rel_id'] = $id;
		
		// attempt the insert
		$result = $comments->insert($values['comments']);
		
		// did it work?
		if (Solar::isError($result)) {
			$err = $result->pop();
			$form->feedback[] = $err['class::code'] . ' -- ' . $err['text'];
		} else {
			// success! reset the form elements to their defaults.
			// (put them in an array called 'comments')
			$form->setElements($comments->formElements('mini'), 'comments');
			$form->feedback[] = Solar::Locale('Solar', 'OK_SAVED');
		}
	}
}

// get the item elements; this will be frozen on display
$tpl->item = $bugs->formElements('edit', $data);

// add the comment form
$tpl->formdata = $form;

// add the existing comments
$tpl->comments = $comments->fetchQueue('sc_bugs', $id);

// are we allowing edits?
$user = Solar::shared('user');

$ok_user = in_array(
	$user->auth->username,
	Solar::config('Solar_App_Bugs', 'admin_user', array())
);

$ok_role = $user->role->inAny(
	Solar::config('Solar_App_Bugs', 'admin_role', array())
);

$tpl->can_edit = $ok_user || $ok_role;

// display
return $tpl->fetch('item.php');
?>