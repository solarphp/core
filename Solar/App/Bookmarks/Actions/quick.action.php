<?php
/**
 * 
 * Controller action script for adding a bookmark from the QuickMark JavaScript.
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

// get the quickmark info from the query
$uri = $this->_query('uri');
$subj = $this->_query('subj');

// get the bookmarks model
$bookmarks = Solar::factory('Solar_Model_Bookmarks');

// we need to see if the user already has the same URI in
// his bookmarks so that we don't add it twice.
$existing = $bookmarks->fetchOwnerUri(
    $user->auth->username,
    $uri
);

// if the user *does* already have that URI bookmarked,
// redirect to the existing bookmark.
/** @todo This won't work in non-base non-index.php cases, will it? */
if (! empty($existing['id'])) {
    $link = Solar::factory('Solar_Uri');
    $link->setInfoString("bookmarks/edit/{$existing['id']}");
    $this->_redirect($link->export());
}

// get a blank bookmark item, build the basic form
$item = $bookmarks->fetchDefault();
$item['uri'] = $uri;
$item['subj'] = $subj;
$form = $bookmarks->form(array('bookmarks' => $item));

// overwrite form defaults with submissions
$form->populate();

// check for a 'Save' operation
$op = Solar::post('op');
if ($op == Solar::locale('Solar', 'OP_SAVE') && $form->validate()) {
    
    // save the data
    try {
    
        // get the form values
        $values = $form->values();
        $data = $values['bookmarks'];
        $data['owner_handle'] = $user->auth->username;
        
        // save
        $result = $bookmarks->save($data);
        
        // redirect to the source URI
        $this->_redirect($uri);
        
    } catch (Solar_Exception $e) {
        
        // exception on save()
        // we should not have gotten to this point,
        // but need to be aware of possible problems.
        $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        
    }
}


// assign data for the view
$this->formdata = $form;
$this->backlink = $uri;

// assign data for the layout
$this->_layout['head']['title'] = 'Solar_App_Bookmarks';
$this->_layout['body']['title'] = $this->locale('BOOKMARKS');
?>