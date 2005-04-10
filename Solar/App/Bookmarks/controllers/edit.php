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
	$tpl->err[] = 'You are not logged in.';
	return $tpl->fetch('error.php');
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) Solar::get('id', 0);

// get the bookmark entry
if ($id) {
	$item = $bookmarks->fetchItem($id);
} else {
	$item = $bookmarks->defaultRow();
	$item['uri'] = Solar::get('uri');
	$item['title'] = Solar::get('title');
	$item['user_id'] = $user->auth->username;
	$item['tags'] = '';
}

// must be the item owner to edit it
if ($user->auth->username != $item['user_id']) {
	$tpl->err[] = 'You do not own this bookmark, or it does not exist.';
	return $tpl->fetch('error.php');
}


// ---------------------------------------------------------------------
// 
// build a link for header('Location: ') calls and the backlink.
// 

// if we came from a tag or user page, return there.
// if we came from a quickmark, return to the originating page.
// otherwise, return the list for the user.
$link = Solar::object('Solar_Uri');

// clear the current pathinfo and query
$link->clearInfo();
$link->clearQuery();

// get any info and query set by the list view
$info = Solar::get('info');
$qstr = Solar::get('qstr');

// get any uri set by a quickmark
$uri = Solar::get('uri');

// by default, no href is set
$href = false;

// do we have info or a qstr?
if ($info || $qstr) {
	// yes, return to a list of bookmarks
	$link->info('setstr', $info);
	$link->query('setstr', $qstr);
	$href = $link->export();
} elseif ($uri) {
	// return to the quickmark uri
	$href = $uri;
} else {
	// return to the user's list
	$link->info('set', 0, 'user');
	$link->info('set', 1, Solar::$shared->user->auth->username);
	$href = $link->export();
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
		$values['bookmarks']['user_id'] = Solar::$shared->user->auth->username;
		
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
			
			// if new, return to the backlink
			if (Solar::get('id', 0) == 0) {
				header("Location: $href");
			}
			
		}
	}
}


// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
	header("Location: $href");
	return;
}

// OP: Delete
if ($op == Solar::locale('Solar', 'OP_DELETE')) {
	$values = $form->values();
	$where = 'id = ' . $bookmarks->quote($values['bookmarks']['id']);
	$bookmarks->delete($where);
	header("Location: $href");
}

// assign the form object
$tpl->formdata = $form;
$tpl->backlink = $href;

// display output
return $tpl->fetch('edit.php');
?>