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
 * @version $Id$
 * 
 */

// must be logged in to proceed
if ($this->_user->auth->status != 'VALID') {
    $this->err[] = 'You are not logged in.';
    return $this->_forward('error');
}

// build a link for _redirect() calls and the backlink.
$href = $this->getFlash('backlink');
if (! $href) {
    // probably browsed to this page directly.  link to the user's list.
    $uri = Solar::factory('Solar_Uri');
    $href = $uri->toAction("bookmarks/user/{$this->_user->auth->handle}");
}

// keep the backlink for the next page load
$this->setFlash('backlink', $href);

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
$submit = Solar::post('submit');

// OP: Save
if ($submit == Solar::locale('Solar', 'SUBMIT_SAVE') && $form->validate()) {
    
    // force owner and editor handles
    $data = $form->values('bookmark');
    $data['owner_handle'] = $this->_user->auth->handle;
    $data['editor_handle'] = $this->_user->auth->handle;
    
    // save the data
    try {
        $result = $this->_bookmarks->save($data);
        
        // retain the id
        $id = $result['id'];
        
        // tell the edit controller that we added successfully
        $this->setFlash('add_ok', true);
        
        // redirect to editing
        $this->_redirect("bookmarks/edit/$id");
        
    } catch (Solar_Exception $e) {
        
        // exception on save()
        // we should not have gotten to this point,
        // but need to be aware of possible problems.
        $form->setStatus(false);
        $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        
    }
}

// OP: Cancel
if ($submit == Solar::locale('Solar', 'SUBMIT_CANCEL')) {
    $this->_redirect($href);
}

// ---------------------------------------------------------------------
// 
// completion
// 

// assign data for the view
$this->formdata = $form;
$this->backlink = $href;
?>