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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

// must be logged in to proceed
if (! $this->_user->auth->isValid()) {
    $this->errors[] = 'You are not logged in.';
    return $this->_forward('error');
}

// get the quickmark info from the query
$uri = $this->_query('uri');
$subj = $this->_query('subj');

// we need to see if the user already has the same URI in
// his bookmarks so that we don't add it twice.
$existing = $this->_bookmarks->fetchByOwnerUri(
    $this->_user->auth->handle,
    $uri
);

// if the user *does* already have that URI bookmarked,
// redirect to the existing bookmark.
if (! empty($existing['id'])) {
    $this->_flash->set('backlink', $uri);
    $this->_redirect("bookmarks/edit/{$existing['id']}");
}

// get a blank bookmark item, build the basic form
$item = $this->_bookmarks->fetchDefault();
$item['uri'] = $uri;
$item['subj'] = $subj;
$form = $this->_bookmarks->form($item);

// overwrite form defaults with submissions
$form->populate();

// check for a 'Save' operation
$submit = Solar::post('submit');
if ($submit == $this->locale('SUBMIT_SAVE') && $form->validate()) {
    
    // save the data
    try {
    
        // get the form values
        $data = $form->values('bookmark');
        $data['owner_handle'] = $this->_user->auth->handle;
        $data['editor_handle'] = $this->_user->auth->handle;
        
        // save
        $result = $this->_bookmarks->save($data);
        
        // redirect to the source URI (external)
        $this->_redirect($data['uri']);
        
    } catch (Solar_Exception $e) {
        
        // exception on save()
        // we should not have gotten to this point,
        // but need to be aware of possible problems.
        $form->setStatus(false);
        $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        echo $e;
        
    }
}


// assign data for the view
$this->formdata = $form;
$this->backlink = $uri;
?>