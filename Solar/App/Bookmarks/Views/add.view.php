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
<h2><?php echo $this->getText('TITLE_ADD_NEW') ?></h2>

<p>[ <?php echo $this->action($this->backlink, 'BACKLINK') ?> ]</p>

<?php echo $this->form()
    ->auto($this->formdata)
    ->hidden(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
    ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
    ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_CANCEL')))
    ->fetch();
?>