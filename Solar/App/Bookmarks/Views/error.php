<?php
/**
 * 
 * Savant3 template for displaying major errors.
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
 * @version $Id: error.php 499 2005-09-22 16:24:55Z pmjones $
 * 
 */

/**
 * 
 * Savant3 template for displaying major errors.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 */
?>
<?php include $this->template('header.php') ?>

<div style="color: red;">
<?php foreach ($this->err as $text): ?>
    <p><?php $this->eprint($text) ?></p>
<?php endforeach ?>
</div>

<?php include $this->template('footer.php') ?>