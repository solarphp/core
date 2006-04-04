<?php
/**
 * 
 * HTML view.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
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
        <?php echo $this->action("hello/main?code=$code", $code) ?>
        (<?php echo $this->action("hello/rss?code=$code", 'RSS') ?>)
    </li>
    <?php endforeach ?>
</ul>
