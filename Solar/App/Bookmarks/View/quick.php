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
 * @version $Id: quick.php 1395 2006-06-27 17:47:33Z pmjones $
 * 
 */
?>
<h1><?php echo $this->getText('HEADING_BOOKMARKS') ?></h1>
<h2><?php echo $this->getText('HEADING_QUICK') ?></h2>
<p>[ <?php echo $this->anchor($this->backlink, 'BACKLINK') ?> ]</p>

<?php
    $attribs = array(
        // using raw string for delete confirmation to avoid double-escaping
        'onclick' => "return confirm('" . $this->getText('CONFIRM_DELETE', null, true) . "')"
    );
    
    echo $this->form()
              ->auto($this->formdata)
              ->hidden(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->submit(array('name' => 'submit', 'value' => $this->getTextRaw('SUBMIT_SAVE')))
              ->fetch();
?>
