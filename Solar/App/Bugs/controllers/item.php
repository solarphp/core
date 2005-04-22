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
	$this->view->error = $data;
	return $this->view('error');
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
$this->view->item = $bugs->formElements('edit', $data);

// add the comment form
$this->view->formdata = $form;

// add the existing comments
$this->view->comments = $comments->fetchQueue('sc_bugs', $id);

// get the shared user object
$user = Solar::shared('user');

// is user allowed to edit?
$ok_user = in_array($user->auth->username, $this->config['admin_user']);

// is user in an editing role?
$ok_role = $user->role->inAny($this->config['admin_role']);

// user may edit if allowed by name or role
$this->view->can_edit = $ok_user || $ok_role;

// display
return $this->view('item');
?>