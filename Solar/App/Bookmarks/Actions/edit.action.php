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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

// must be logged in to proceed
if ($this->_user->auth->status != 'VALID') {
    $this->errors[] = 'You are not logged in.';
    return $this->_forward('error');
}

// get the bookmark ID (0 means a new bookmark)
$id = (int) $this->_info('id', 0);
if (! $id) {
    $this->errors[] = 'No bookmark selected for editing.';
    return $this->_forward('error');
}


// must be the item owner to edit it
$item = $this->_bookmarks->fetch($id);
if ($this->_user->auth->handle != $item['owner_handle']) {
    $this->errors[] = 'You do not own this bookmark, or it does not exist.';
    return $this->_forward('error');
}

// ---------------------------------------------------------------------
// 
// build a link for _redirect() calls and the backlink.
// 
// if we came from a tag or user page, return there.
// otherwise, return the list for the user.
//

$href = $this->getFlash('backlink');
if (! $href) {
    // probably browsed directly to this page; return to the user's list
    $uri = Solar::factory('Solar_Uri_Action');
    $href = $uri->quick("bookmarks/user/{$this->_user->auth->handle}");
}

// keep the backlink for the next page load
$this->setFlash('backlink', $href);

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
$submit = Solar::post('submit');

// OP: Save
if ($submit == Solar::locale('Solar', 'SUBMIT_SAVE') && $form->validate()) {
    
    // force owner and editor values
    $data = $form->values('bookmark');
    $data['owner_handle'] = $this->_user->auth->handle;
    $data['editor_handle'] = $this->_user->auth->handle;
    
    // save the data
    try {
        
        // attempt the save, may throw an exception
        $result = $this->_bookmarks->save($data);
        
        // retain the id
        $id = $data['id'];
    
        // if new, return to the backlink
        if ($this->_info('id', 0) == 0) {
            $this->_redirect($href);
        }
        
    } catch (Solar_Sql_Table_Exception $e) {
        
        // exception on save()
        // we should not have gotten to this point,
        // but need to be aware of possible problems.
        $form->setStatus(false);
        $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        
        // add bookmark[*] element feedback
        $form->addFeedback($e->getInfo(), 'bookmark');
    }
}

// OP: Cancel
if ($submit == Solar::locale('Solar', 'SUBMIT_CANCEL')) {
    $this->_redirect($href);
}

// OP: Delete
if ($submit == Solar::locale('Solar', 'SUBMIT_DELETE')) {
    $values = $form->values();
    $id = $values['bookmark']['id'];
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
?>