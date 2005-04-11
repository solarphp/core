<?php

/**
* 
* Controller action script for editing a bug report.
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
* Controller action script for editing a bug report.
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

// ---------------------------------------------------------------------
// 
// preliminaries: permission checks
// 

// get the bug report ID (0 means a new report)
$id = (int) Solar::get('id', 0);

// is user ok by username?
$ok_user = in_array(
	$user->auth->username,
	Solar::config('Solar_App_Bugs', 'admin_user', array())
);

// is user ok by role?
$ok_role = $user->role->inAny(
	Solar::config('Solar_App_Bugs', 'admin_role', array())
);

// return if not OK (anyone is allowed to edit $id = 0, that's a new report)
if (! $ok_user && ! $ok_role && $id != 0) {
	return Solar::locale('Solar_App_Bugs', 'ERR_NOT_ADMIN');
}


// ---------------------------------------------------------------------
// 
// main section
// 

// the form type to use
$formtype = '';

if ($id) {
	
	// it's an edit form
	$formtype = 'edit';
	
	// get from existing report.
	$form->setElements(
		$bugs->formElements('edit', $bugs->fetchItem($id)),
		'bugs'
	);
	
} else {
	
	// it's a new-entry form
	$formtype = 'new';
	
	// new bug report.
	$data = $bugs->defaultRow();

	// allow fields to pre-fill from GET vars.
	foreach ($data as $key => $val) {
		$tmp = Solar::get($key);
		if ($tmp) {
			$data[$key] = $tmp;
		}
	}
	
	// add elements from the default row to the form
	$form->setElements(
		$bugs->formElements('new', $data),
		'bugs'
	);
}

// add elements from $comments in an array called 'comments'
$form->setElements(
	$comments->formElements('mini', $comments->defaultRow()),
	'comments'
);

// bring in the submitted values to the form
$form->populate();

// what operation are we performing?
$op = Solar::post('op');

// OP: Save
if ($op == Solar::locale('Solar', 'OP_SAVE')) {
	
	if (! $form->validate()) {
	
		$form->feedback[] = Solar::locale('Solar', 'ERR_FORM');
		
	} else {
	
		$values = $form->values();
		
		// new report, or modify old report?
		if ($values['bugs']['id']) {
		
			// modify old report
			$result = $bugs->updateItem(
				$values['bugs'], // the data
				$values['bugs']['id'] // the ID number
			);
			$id = $values['bugs']['id'];
			
		} else {
		
			// add new report. force the status to 'new'
			$values['bugs']['status'] = 'new';
			$result = $bugs->insert($values['bugs']);
			$id = $result;
		}
		
		// were there errors?
		if (Solar::isError($result)) {
			
			// get the error array
			$err = $result->pop();
			// capture the feedback text
			$form->feedback[] = $err['class::code'] . ' -- ' . $err['text'];
			
		} else {
			
			// it worked!  $result is the new ID number.
			$form->feedback[] = Solar::locale('Solar', 'OK_SAVED');
			
			// now add the comment, if there was one.
			// $id was set when up updated the report above.
			if (trim($values['comments']['body']) != '') {
				$data = array(
					'rel'    => 'sc_bugs',
					'rel_id' => $id,
					'email'  => $values['comments']['email'],
					'subj'   => $values['bugs']['summ'],
					'body'   => $values['comments']['body']
				);
				$comments->insert($data);
			}
			
			// redirect to 'view item'
			header('Location: ?action=item&id=' . $id);
			return;
		}
	}
}

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
	if ($id == 0) {
		$location = '?action=listOpen';
	} else {
		$location = "?action=item&id=$id";
	}
	header("Location: $location");
	return;
}

// get comments about the bug
$id = $form->elements['bugs[id]']['value'];
$tpl->comments = $comments->fetchQueue('sc_bugs', $id);

// assign the form object
$tpl->formdata = $form;

// display output
return $tpl->fetch('edit.php');
?>