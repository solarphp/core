<?php
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
// (put them in an array called 'talk')
$form->setElements($talk->formElements('mini'), 'talk');

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
		$values['talk']['queue'] = "sc_bugs://$id";
		$values['talk']['subj'] = $data['summ'];
		
		// attempt the insert
		$result = $talk->insert($values['talk']);
		
		// did it work?
		if (Solar::isError($result)) {
			echo "FAILED";
			$err = $result->pop();
			$form->feedback[] = $err['class::code'] . ' -- ' . $err['text'];
			Solar::dump($form);
		} else {
			// success! reset the form elements to their defaults.
			// (put them in an array called 'talk')
			$form->setElements($talk->formElements('mini'), 'talk');
			$form->feedback[] = Solar::Locale('Solar', 'OK_SAVED');
		}
	}
}

// get the item elements; this will be frozen on display
$tpl->item = $bugs->formElements('edit', $data);

// add the comment form
$tpl->formdata = $form;

// add the existing comments
$tpl->comments = $talk->fetchQueue("sc_bugs://$id");

// are we allowing edits?
$user = Solar::shared('user');

$ok_user = in_array(
	$user->auth->username,
	Solar::config('Solar_App_Bugs', 'admin_user')
);

$ok_role = $user->role->inAny(
	Solar::config('Solar_App_Bugs', 'admin_role')
);

$tpl->can_edit = $ok_user || $ok_role;

// display
$tpl->setTemplate('item.php');
echo $tpl;
?>