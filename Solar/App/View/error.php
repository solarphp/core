<?php
/**
 * 
 * Generic application view for displaying major errors.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: error.php 1344 2006-06-22 23:00:03Z pmjones $
 * 
 */
?>
<div class='error'>
<?php foreach ($this->errors as $text): ?>
    <p><?php echo $this->escape($text) ?></p>
<?php endforeach ?>
</div>
