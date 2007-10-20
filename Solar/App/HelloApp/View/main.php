<?php
/**
 * 
 * HTML view.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloApp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
<p><?php echo $this->escape($this->text) ?></p>
<p><?php echo $this->escape($this->code) ?></p>
<ul>
    <?php foreach ($this->list as $code): ?>
    <li>
        <?php echo $this->action("hello-app/main/$code", $code) ?>
        (<?php echo $this->action("hello-app/rss/$code", 'RSS') ?>)
    </li>
    <?php endforeach ?>
</ul>
