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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */
?>
<h1><?php echo $this->getText('BOOKMARKS') ?></h1>
<h2><?php echo $this->getText('EDIT_ITEM') ?></h2>
<p>[ <?php echo $this->action($this->backlink, 'BACKLINK') ?> ]</p>

<?php
    $attribs = array(
        // using raw string for delete confirmation to avoid double-escaping
        'onclick' => "return confirm('" . $this->getText('CONFIRM_DELETE', null, true) . "')"
    );
    
    echo $this->form()
              ->auto($this->formdata)
              ->hidden(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->beginGroup()
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_CANCEL')))
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_DELETE'), 'attribs' => $attribs))
              ->endGroup()
              ->fetch();
?>
