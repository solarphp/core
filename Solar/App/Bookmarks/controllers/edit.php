<?php

/**
* 
* Controller action script for editing a bookmark.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
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
* Controller action script for editing a bookmark.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
*/

// prepend for all controllers
include $this->helper('prepend');

// ---------------------------------------------------------------------
// 
// preliminaries: permission checks
// 

// must be logged in to proceed
if ($user->auth->status_code != 'VALID') {
	return 'Not logged in.';
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) Solar::get('id', 0);

// get the bookmark entry
if ($id) {
	$item = $bookmarks->item($id);
} else {
	$item = $bookmarks->defaultRow();
	$item['uri'] = Solar::get('uri');
	$item['title'] = Solar::get('title');
	$item['user_id'] = $user->auth->username;
	$item['tags'] = '';
}

// must be the item owner to edit it
if ($user->auth->username != $item['user_id']) {
	return 'You do not own this bookmark, or it does not exist.';
}


// ---------------------------------------------------------------------
// 
// main section
// 

// build the basic form
$form->setElements(
	$bookmarks->formElements('edit', $item),
	'bookmarks'
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
		
		// new bookmark, or modify old bookmark?
		if ($values['bookmarks']['id']) {
		
			// modify old bookmark
			$result = $bookmarks->update(
				$values['bookmarks'], // the data
				$values['bookmarks']['id'] // the ID number
			);
			
			$id = $values['bookmarks']['id'];
			
		} else {
		
			// add new bookmark.
			$result = $bookmarks->insert($values['bookmarks']);
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
			
		}
	}
}

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
	$self = Solar::server('PHP_SELF');
	header("Location: $self");
	return;
}

// assign the form object
$tpl->formdata = $form;

// display output
return $tpl->fetch('edit.php');
?>