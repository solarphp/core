<?php
/**
 * 
 * Solar_View_Xhtml template for editing a bookmark.
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
?>
<h2><?php echo $this->getText('EDIT_ITEM') ?></h2>
<p>[ <?php echo $this->anchor($this->backlink, 'BACKLINK') ?> ]</p>

<?php
    $attribs = array(
        // using raw string for delete confirmation to avoid double-escaping
        'onclick' => "return confirm('" . $this->getText('CONFIRM_DELETE', null, true) . "')"
    );
    
    echo $this->form()
              ->auto($this->formdata)
              ->hidden(array('name' => 'op', 'value' => $this->getTextRaw('OP_SAVE')))
              ->beginGroup()
              ->submit(array('name' => 'op', 'value' => $this->getTextRaw('OP_SAVE')))
              ->submit(array('name' => 'op', 'value' => $this->getTextRaw('OP_CANCEL')))
              ->submit(array('name' => 'op', 'value' => $this->getTextRaw('OP_DELETE'), 'attribs' => $attribs))
              ->endGroup()
              ->fetch();
?>
