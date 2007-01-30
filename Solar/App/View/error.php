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
 * @version $Id: error.php 1379 2006-06-24 19:32:44Z pmjones $
 * 
 */
?>
<div class='error'>
<?php foreach ($this->errors as $code): ?>
    <p><?php echo $this->getText($code) ?></p>
<?php endforeach ?>
</div>
