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
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
?>
<h2><?php echo $this->getText('TITLE_ADD_NEW') ?></h2>

<p>[ <?php echo $this->anchor($this->backlink, 'BACKLINK') ?> ]</p>

<?php echo $this->form()
    ->auto($this->formdata)
    ->hidden(array('name' => 'op', 'value' => $this->getTextRaw('OP_SAVE')))
    ->submit(array('name' => 'op', 'value' => $this->getTextRaw('OP_SAVE')))
    ->submit(array('name' => 'op', 'value' => $this->getTextRaw('OP_CANCEL')))
    ->fetch();
?>