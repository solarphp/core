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
 * @version $Id$
 * 
 */
?>
<div class='error'>
<?php foreach ($this->errors as $code): ?>
    <p><?php echo $this->getText($code) ?></p>
<?php endforeach ?>
</div>
