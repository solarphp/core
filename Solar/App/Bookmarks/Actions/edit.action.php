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

// get the shared user object
$user = Solar::registry('user');

// get standalone objects
$bookmarks = Solar::factory('Solar_Model_Bookmarks');

// allow uri to set the "count" for each page (default 10)
$bookmarks->paging($this->_query('paging', 10));

// ---------------------------------------------------------------------
// 
// preliminaries: permission checks
// 

// must be logged in to proceed
if ($user->auth->status_code != 'VALID') {
    $this->err[] = 'You are not logged in.';
    $this->_view = 'error';
    return;
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) $this->_info('id', 0);

// get the bookmark node entry
if ($id) {
    $item = $bookmarks->fetchItem($id);
} else {
    $item = $bookmarks->fetchDefault();
}

// must be the item owner to edit it
if ($user->auth->username != $item['owner_handle']) {
    $this->err[] = 'You do not own this bookmark, or it does not exist.';
    $this->_view = 'error';
    return;
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
        $this->_query('uri')
    );
    
    // if the user *does* already have that URI bookmarked,
    // redirect to the existing bookmark.
    if (! empty($existing['id'])) {
        $link = Solar::factory('Solar_Uri');
        $link->setInfoString("bookmarks/edit/{$existing['id']}");
        $this->_redirect($link->export());
    }
}

// ---------------------------------------------------------------------
// 
// build a link for _redirect() calls and the backlink.
// 
// if we came from a tag or user page, return there.
// if we came from a quickmark, return to the originating page.
// otherwise, return the list for the user.
//

// get the current link (i.e., to this page)
$link = Solar::factory('Solar_Uri');

// clear the current pathinfo and query
$link->clearInfo();
$link->clearQuery();

// get any info and query set by the list view
$info = $this->_query('info');
$qstr = $this->_query('qstr');

// get any uri set by a quickmark
$uri = $this->_query('uri');

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
    $link->setInfoString("bookmarks/user/{$user->auth->username}");
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
        
        // save the data
        try {
            $result = $bookmarks->save($data);
            
            // retain the id
            $id = $data['id'];
        
            // it worked!  $result is the new ID number.
            $form->feedback[] = Solar::locale('Solar', 'OK_SAVED');
            
            // if new, return to the backlink
            if ($this->_info('id', 0) == 0) {
                $this->_redirect($href);
            }
            
        } catch (Solar_Exception $e) {
            
            // exception on save()
            // we should not have gotten to this point,
            // but need to be aware of possible problems.
            $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
            
        }
    }
}

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
    $this->_redirect($href);
}

// OP: Delete
if ($op == Solar::locale('Solar', 'OP_DELETE')) {
    $values = $form->values();
    $id = $values['bookmarks']['id'];
    $bookmarks->delete($id);
    $this->_redirect($href);
}

// ---------------------------------------------------------------------
// 
// completion
// 

// assign data for the view
$this->formdata = $form;
$this->backlink = $href;

// assign data for the layout
$this->_layout['head']['title'] = 'Solar_App_Bookmarks';
$this->_layout['body']['header'] = $this->locale('BOOKMARKS');
?>