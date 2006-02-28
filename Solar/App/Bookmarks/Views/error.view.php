<?php
/**
 * 
 * Solar_View_Xhtml template for displaying major errors.
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

<div style="color: red;">
<?php foreach ($this->err as $text): ?>
    <p><?php echo $this->escape($text) ?></p>
<?php endforeach ?>
</div>
