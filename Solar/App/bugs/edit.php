<?php

// startup
require_once 'Solar.php';
Solar::start();

// ---------------------------------------------------------------------
// 
// preliminaries: permission checks
// 

// get the shared user object
$user = Solar::shared('user');

// is user ok by username?
$ok_user = in_array(
	$user->auth->username,
	Solar::config('Solar_App_Bugs', 'admin_user')
);

// is user ok by role?
$ok_role = $user->role->inAny(
	Solar::config('Solar_App_Bugs', 'admin_role')
);

// die if not OK
if (! $ok_user && ! $ok_role) {
	echo "NOT ADMIN";
	Solar::stop();
	die();
}


// ---------------------------------------------------------------------
// 
// main section
// 

// get a shared template object
$tpl = Solar::shared('template');
$tpl->addPath('template', './tpl/');

// get other standalone objects
$bugs = Solar::object('Solar_Cell_Bugs');
$talk = Solar::object('Solar_Cell_Talk');
$form = Solar::object('Solar_Form');

// the form type to use
$formtype = '';

// get the inital form values from the table, or as a new report?
$id = Solar::get('id');
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

// add elements from $talk in an array called 'talk'
$form->setElements(
	$talk->formElements('mini', $talk->defaultRow()),
	'talk'
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
			if (trim($values['talk']['body']) != '') {
				$data = array(
					'queue' => "sc_bugs://$id",
					'email' => $values['talk']['email'],
					'subj' => $values['bugs']['summ'],
					'body' => $values['talk']['body']
				);
				$talk->insert($data);
			}
			
			// redirect to 'view'
			header('Location: view.php?id=' . $id);
			exit;
			
			/*
			// re-get the template values from the database.
			$form->setElements(
				$bugs->formElements('edit', $bugs->fetchItem($id)),
				'bugs'
			);
			
			// clear out the talk portion.
			$form->setElements(
				$talk->formElements('mini', $talk->defaultRow()),
				'talk'
			);
			*/
		}
	}
}

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
	header('Location: index.php');
}

// get comments about the bug
$id = $form->elements['bugs[id]']['value'];
$tpl->comments = $talk->fetchQueue("sc_bugs://$id");

// assign the form object
$tpl->formdata = $form;

// display output
$tpl->setTemplate('edit.tpl.php');
echo $tpl;

// shutdown
Solar::stop();
?>