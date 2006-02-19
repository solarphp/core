<?php
/**
 * 
 * Controller action script for editing an existing bookmark.
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

// must be logged in to proceed
if ($this->_user->auth->status_code != 'VALID') {
    $this->err[] = 'You are not logged in.';
    return $this->_forward('error');
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) $this->_info('id', 0);
if (! $id) {
    $this->err[] = 'No bookmark selected for editing.';
    return $this->_forward('error');
}


// must be the item owner to edit it
$item = $this->_bookmarks->fetchItem($id);
if ($this->_user->auth->username != $item['owner_handle']) {
    $this->err[] = 'You do not own this bookmark, or it does not exist.';
    return $this->_forward('error');
}

// ---------------------------------------------------------------------
// 
// build a link for _redirect() calls and the backlink.
// 
// if we came from a tag or user page, return there.
// if we came from a quickmark, return to the originating page.
// otherwise, return the list for the user.
//

$uri = $this->_query('uri');
$href = $this->getFlash('backlink');
if ($uri) {
    $href = $uri;
} elseif (! $href) {
    // return to the user's list
    $link = Solar::factory('Solar_Uri');
    $link->setInfoString("bookmarks/user/{$this->_user->auth->username}");
    $href = $link->export();
}

// ---------------------------------------------------------------------
// 
// operations
// 

// build the basic form, populated with the bookmark data
// from the database
$form = $this->_bookmarks->form($item);

// now populate the the submitted POST values to the form
$form->populate();

// what operation are we performing?
$op = Solar::post('op');

// OP: Save
if ($op == Solar::locale('Solar', 'OP_SAVE') && $form->validate()) {
    
    $values = $form->values();
    $data = $values['bookmark'];
    
    // force a user_id
    $data['owner_handle'] = $this->_user->auth->username;
    
    // save the data
    try {
        $result = $this->_bookmarks->save($data);
        
        // retain the id
        $id = $data['id'];
    
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

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
    $this->_redirect($href);
}

// OP: Delete
if ($op == Solar::locale('Solar', 'OP_DELETE')) {
    $values = $form->values();
    $id = $values['bookmarks']['id'];
    $this->_bookmarks->delete($id);
    $this->_redirect($href);
}

// ---------------------------------------------------------------------
// 
// completion
// 

// assign data for the view
$this->formdata = $form;
$this->backlink = $href;

// keep the backlink for the next page load
$this->setFlash('backlink', $href);

// assign data for the layout
$this->_layout['head']['title'] = 'Solar_App_Bookmarks';
$this->_layout['body']['title'] = $this->locale('BOOKMARKS');
?>