<?php
/**
* 
* Savant3 template for displaying major errors.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
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
* @subpackage Solar_App_Bugs
* 
*/
?>
<?php include $this->template('header.php') ?>

<div style="color: red;">
<?php foreach ($this->err as $text): ?>
	<p><?php echo $this->scrub($text) ?></p>
<?php endforeach ?>
</div>

<?php include $this->template('footer.php') ?>