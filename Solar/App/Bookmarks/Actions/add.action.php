<?php
/**
 * 
 * Controller action script for adding a new bookmark.
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
 * @version $Id: edit.action.php 768 2006-02-09 03:29:35Z pmjones $
 * 
 */

// get the shared user object
$user = Solar::registry('user');

// must be logged in to proceed
if ($user->auth->status_code != 'VALID') {
    $this->err[] = 'You are not logged in.';
    return $this->_forward('error');
}

// build a link for _redirect() calls and the backlink.
$href = $this->getFlash('backlink');
if ( ! $href) {
    // return to the user's list
    $link = Solar::factory('Solar_Uri');
    $link->setInfoString("bookmarks/user/{$user->auth->username}");
    $href = $link->export();
}

// build the basic form, populated with the bookmark data
// from the database
$item = $this->_bookmarks->fetchDefault();
$form = $this->_bookmarks->form($item);

// now populate the the submitted POST values to the form
$form->populate();


// ---------------------------------------------------------------------
// 
// operations
// 

// what operation are we performing?
$op = Solar::post('op');

// OP: Save
if ($op == Solar::locale('Solar', 'OP_SAVE') && $form->validate()) {
    
    $values = $form->values();
    $data = $values['bookmark'];
    
    // force a user_id
    $data['owner_handle'] = $user->auth->username;
    
    // save the data
    try {
        $result = $this->_bookmarks->save($data);
        
        // retain the id
        $this->_info['id'] = $result['id'];
        
        // tell the edit controller that we added successfully
        $this->setFlash('add_ok', true);
        
        // forward to editing
        return $this->_forward('edit');
        
    } catch (Solar_Exception $e) {
        
        // exception on save()
        // we should not have gotten to this point,
        // but need to be aware of possible problems.
        $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        echo $e;
        
    }
}

// OP: Cancel
if ($op == Solar::locale('Solar', 'OP_CANCEL')) {
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