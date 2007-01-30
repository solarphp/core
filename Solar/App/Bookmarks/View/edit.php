<?php
/**
 * 
 * Solar_View template for editing a bookmark.
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
 * @version $Id: edit.php 1842 2006-09-24 18:05:07Z pmjones $
 * 
 */
?>
<h1><?php echo $this->getText('HEADING_BOOKMARKS') ?></h1>
<h2><?php echo $this->getText('HEADING_EDIT') ?></h2>
<p>[ <?php echo $this->anchor($this->backlink, 'BACKLINK') ?> ]</p>

<?php
    $attribs = array(
        // using raw string for delete confirmation to avoid double-escaping
        'onclick' => "return confirm('" . $this->getTextRaw('CONFIRM_DELETE') . "')"
    );
    
    echo $this->form(array('id' => 'form-bookmark'))
              ->auto($this->formdata)
              ->hidden(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->beginGroup()
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_CANCEL')))
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_DELETE'), 'attribs' => $attribs))
              ->endGroup()
              ->fetch();
    
    // add highlighting to all UL elements in the form.
    // this works for success and failure, and for all
    // individual failed elements.
    $this->jsScriptaculous()->effect->highlight(
        "#form-bookmark ul.success",
        array(
            'duration' => 3,
            'endcolor' => '#aaaaff',
            'restorecolor' => true,
        )
    );
    
    $this->jsScriptaculous()->effect->highlight(
        "#form-bookmark ul.failure",
        array(
            'duration' => 3,
            'endcolor' => '#ffaaaa',
            'restorecolor' => true,
        )
    );
    
?>
