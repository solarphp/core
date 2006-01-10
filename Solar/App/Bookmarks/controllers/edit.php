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
 * Prepend for all controllers.
 */
include $this->helper('prepend');

// ---------------------------------------------------------------------
// 
// preliminaries: permission checks
// 

// must be logged in to proceed
if ($user->auth->status_code != 'VALID') {
    $this->view->err[] = 'You are not logged in.';
    return $this->view('error');
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) Solar::get('id', 0);

// get the bookmark node entry
if ($id) {
    $item = $bookmarks->fetchItem($id);
} else {
    $item = $bookmarks->fetchDefault();
}

// must be the item owner to edit it
if ($user->auth->username != $item['owner_handle']) {
    $this->view->err[] = 'You do not own this bookmark, or it does not exist.';
    return $this->view('error');
}

// ---------------------------------------------------------------------
// 
// if this is a new-bookmark request, but the URI is already bookmarked
// for the user, redirect to that bookmark ID.
// 

if (! $id) {
    
    // there's no ID, but this might be an incoming QuickMark.
    // we need to see if the user already has the same URI in
    // his bookmarks so that we don't add it twice.
    $existing = $bookmarks->fetchOwnerUri(
        $user->auth->username,
        Solar::get('uri')
    );
    
    // if the user *does* already have that URI bookmarked,
    // redirect to the existing bookmark.
    if (! empty($existing['id'])) {
        $link = Solar::object('Solar_Uri');
        $link->setQuery('id', $existing['id']);
        header('Location: ' . $link->export());
    }

}

// ---------------------------------------------------------------------
// 
// build a link for header('Location: ') calls and the backlink.
// 
// if we came from a tag or user page, return there.
// if we came from a quickmark, return to the originating page.
// otherwise, return the list for the user.
//

// get the current link (i.e., to this page)
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
    $link->setInfoString($info);
    $link->setQueryString($qstr);
    $href = $link->export();
} elseif ($uri) {
    // return to the quickmark uri
    $href = $uri;
} else {
    // return to the user's list
    $link->setInfo(0, 'user');
    $link->setInfo(1, $user->auth->username);
    $href = $link->export();
}


// ---------------------------------------------------------------------
// 
// operations
// 

// build the basic form, populated with the bookmark data
// from the database
$form = $bookmarks->form(array('bookmarks' => $item));

// now populate the the submitted POST values to the form
$form->populate();

// what operation are we performing?
$op = Solar::post('op');

// OP: Save
if ($op == Solar::locale('Solar', 'OP_SAVE')) {
    
    // is the form data valid?
    if (! $form->validate()) {
        
        $form->feedback[] = Solar::locale('Solar', 'ERR_FORM');
        
    } else {
    
        $values = $form->values();
        $data = $values['bookmarks'];
        
        // force a user_id
        $data['owner_handle'] = $user->auth->username;
        
        /*
        // new bookmark, or modify old bookmark?
        if ($data['id']) {
        
            // modify old bookmark
            $result = $bookmarks->update(
                $data['id'], // the ID number
                $data // the data
            );
            
            $id = $data['id'];
            
        } else {
            
            // add new bookmark.
            $result = $bookmarks->insert($data);
            $id = $data['id'];
        }
         */
        $result = $bookmarks->save($data);
        $id = $data['id'];
        
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
    $id = $values['bookmarks']['id'];
    $bookmarks->delete($id);
    header("Location: $href");
}

// ---------------------------------------------------------------------
// 
// completion
// 

// assign data to the view
$this->view->formdata = $form;
$this->view->backlink = $href;

// return the output
return $this->view('edit');
?>